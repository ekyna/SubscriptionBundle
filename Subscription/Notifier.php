<?php

namespace Ekyna\Bundle\SubscriptionBundle\Subscription;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Notifier
 * @package Ekyna\Bundle\SubscriptionBundle\Subscription
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Notifier
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var \Swift_Mailer $mailer
     */
    protected $mailer;

    /**
     * @var SettingsManagerInterface
     */
    protected $settings;

    /**
     * @var string
     */
    protected $subscriptionClass;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface   $em
     * @param TranslatorInterface      $translator
     * @param UrlGeneratorInterface    $router
     * @param EngineInterface          $templating
     * @param \Swift_Mailer            $mailer
     * @param SettingsManagerInterface $settings
     * @param string                   $subscriptionClass
     * @param array                    $config
     */
    public function __construct(
        EntityManagerInterface   $em,
        TranslatorInterface      $translator,
        UrlGeneratorInterface    $router,
        EngineInterface          $templating,
        \Swift_Mailer            $mailer,
        SettingsManagerInterface $settings,
        $subscriptionClass,
        array $config
    ) {
        $this->em                = $em;
        $this->translator        = $translator;
        $this->router            = $router;
        $this->templating        = $templating;
        $this->mailer            = $mailer;
        $this->settings          = $settings;

        $this->subscriptionClass = $subscriptionClass;
        $this->config            = $config;
    }

    /**
     * Sends the "call user for payment" email.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function sendCallUserForPayment(UserInterface $user)
    {
        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface[] $subscriptions */
        $subscriptions = $this
            ->getSubscriptionRepository()
            ->findByUserAndPaymentRequired($user)
        ;
        if (empty($subscriptions)) {
            return true;
        }

        $fromEmail = $this->settings->getParameter('notification.from_email');
        $fromName = $this->settings->getParameter('notification.from_name');

        $content = $this->templating->render($this->config['templates']['call_user_for_payment'], array(
            'user' => $user,
            'subscriptions' => $subscriptions,
        ));

        $message = \Swift_Message::newInstance();
        $message
            ->setFrom($fromEmail, $fromName)
            ->setTo($user->getEmail(), (string) $user)
            ->setSubject($this->translator->trans('ekyna_subscription.email.call_user_for_payment.subject'))
            ->setBody($content, 'text/html')
        ;

        if (0 < $this->mailer->send($message)) {
            foreach ($subscriptions as $subscription) {
                $subscription->setNotifiedAt(new \DateTime());
                $this->em->persist($subscription);
            }
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Returns the subscription repository.
     *
     * @return \Ekyna\Bundle\SubscriptionBundle\Entity\SubscriptionRepository
     */
    protected function getSubscriptionRepository()
    {
        return $this->em->getRepository($this->subscriptionClass);
    }
}
