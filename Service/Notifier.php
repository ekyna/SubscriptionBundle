<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Bundle\SubscriptionBundle\Entity\Notification;
use Ekyna\Bundle\SubscriptionBundle\Entity\Reminder;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

use function get_class;

/**
 * Class Notifier
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Notifier
{
    public function __construct(
        private readonly RenewalHelper           $renewalHelper,
        private readonly DocumentGenerator       $documentGenerator,
        private readonly FactoryFactoryInterface $factoryFactory,
        private readonly ManagerFactoryInterface $managerFactory,
        private readonly Mailer                  $mailer,
    ) {
    }

    public function remind(SubscriptionInterface $subscription, Reminder $reminder): void
    {
        if (!$subscription->isAutoNotify()) {
            return;
        }

        // Don't notify canceled subscription
        if (SubscriptionStates::STATE_CANCELLED === $subscription->getState()) {
            return;
        }

        $renewal = null;

        // Find renewal posterior to expiration date
        $expiresAt = $subscription->getExpiresAt();
        foreach ($subscription->getRenewals() as $subRenewal) {
            if ($expiresAt > $subRenewal->getStartsAt()) {
                continue;
            }

            // (Should never happen) Abort if posterior renewal is paid
            if ($subRenewal->isPaid()) {
                return;
            }

            foreach ($subRenewal->getNotifications() as $notification) {
                if ($notification->getReminder() === $reminder) {
                    // Abort as renewal has already been reminded
                    return;
                }
            }

            $renewal = $subRenewal;

            break;
        }

        if (null === $renewal) {
            // Create renewal and order
            $renewal = $this->createRenewal($subscription);
        }

        $this->notify($renewal, $reminder);
    }

    public function notify(RenewalInterface $renewal, ReminderInterface $reminder): void
    {
        if ($renewal->isPaid()) {
            return;
        }

        $subscription = $renewal->getSubscription();

        if (!$subscription->isAutoNotify()) {
            return;
        }

        // Don't notify canceled subscription
        if (SubscriptionStates::STATE_CANCELLED === $subscription->getState()) {
            return;
        }

        if (!$reminder->isEnabled()) {
            return;
        }

        // Log notification
        $notification = NotificationHelper::findRenewalNotificationByReminder($renewal, $reminder);
        if (null === $notification) {
            $notification = new Notification();
            $notification->setReminder($reminder);
            $renewal->addNotification($notification);
        }

        $notification->setNotifiedAt(new DateTime());

        // Save renewal
        $event = $this
            ->managerFactory
            ->getManager(get_class($renewal))
            ->save($renewal);

        if ($event->isPropagationStopped()) {
            // TODO Log error message if any ?
            return;
        }

        // Generate quote attachment
        $this->generateQuote($renewal->getOrder());

        $this->mailer->sendNotification($notification);
    }

    private function generateQuote(OrderInterface $order): void
    {
        if (DocumentUtil::findWithType($order, DocumentTypes::TYPE_QUOTE)) {
            return;
        }

        try {
            $attachment = $this
                ->documentGenerator
                ->generate($order, DocumentTypes::TYPE_QUOTE);
        } catch (InvalidArgumentException|PdfException) {
            return;
        }

        $this
            ->managerFactory
            ->getManager(get_class($attachment))
            ->save($attachment);
    }

    private function createRenewal(SubscriptionInterface $subscription): RenewalInterface
    {
        // Create renewal and order
        $renewal = $this
            ->factoryFactory
            ->getFactory(RenewalInterface::class)
            ->create()
            ->setSubscription($subscription);

        $order = $this->renewalHelper->renew($renewal);

        if ($subscription !== $renewal->getSubscription()) {
            // Persist previous subscription
            $this->persist($subscription);

            $subscription = $renewal->getSubscription();

            // Persist new subscription
            $this->persist($subscription);
        }

        // Persist order
        $this->persist($order);

        return $renewal;
    }

    private function persist(ResourceInterface $resource): void
    {
        $this
            ->managerFactory
            ->getManager(get_class($resource))
            ->persist($resource);
    }
}
