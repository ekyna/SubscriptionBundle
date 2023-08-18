<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Ekyna\Bundle\AdminBundle\Service\Mailer\MailerHelper as AdminMailerHelper;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\MailerHelper as CommerceMailerHelper;
use Ekyna\Bundle\SubscriptionBundle\Entity\Notification;
use Ekyna\Bundle\UserBundle\Service\Security\LoginLinkHelper;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function strtr;

/**
 * Class Mailer
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Mailer
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoginLinkHelper       $loginLinkHelper,
        private readonly Environment           $twig,
        private readonly AdminMailerHelper     $adminMailerHelper,
        private readonly CommerceMailerHelper  $commerceMailerHelper,
        private readonly FormatterFactory      $formatterFactory,
        private readonly MailerInterface       $mailer,
    ) {
    }

    public function sendNotification(Notification $notification): void
    {
        $reminder = $notification->getReminder();
        $renewal = $notification->getRenewal();
        $order = $renewal->getOrder();
        $locale = $order->getLocale();

        $link = null;
        if (null !== $user = $order->getCustomer()?->getUser()) {
            $uri = $this
                ->urlGenerator
                ->generate('ekyna_commerce_account_order_read', [
                    'number' => $order->getNumber(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);

            $link = $this->loginLinkHelper->createLoginLink($user, $uri);
        }

        $body = $this->twig->render('@EkynaSubscription/Email/remind.html.twig', [
            'reminder'   => $reminder,
            'order'      => $order,
            'login_link' => $link,
        ]);

        $formatter = $this->formatterFactory->create($locale);

        /**
         * Keep is sync with content field help text.
         * @see \Ekyna\Bundle\SubscriptionBundle\Form\Type\ReminderTranslationType::buildForm
         */
        $expiresAt = $renewal->getSubscription()->getExpiresAt();
        $cancelsAt = (clone $expiresAt)->modify('+1 month');
        $replacements = [
            '{expiresAt}' => $formatter->date($expiresAt),
            '{cancelsAt}' => $formatter->date($cancelsAt),
        ];

        $body = strtr($body, $replacements);

        $email = new Email();
        $email->subject($reminder->translate($locale)->getTitle());
        $email->html($body);
        $email->from($this->adminMailerHelper->getNotificationSender());
        $email->to($order->getEmail());

        if (null !== $attachment = DocumentUtil::findWithType($order, DocumentTypes::TYPE_QUOTE)) {
            $this->commerceMailerHelper->attach($email, $attachment);
        }

        $this->mailer->send($email);
    }
}
