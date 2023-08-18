<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Ekyna\Bundle\SubscriptionBundle\Entity\Notification;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\NotificationRepository;

/**
 * Class NotificationHelper
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotificationHelper
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository
    ) {
    }

    public function findSubscriptionNotifications(SubscriptionInterface $subscription, int $limit = 3): array
    {
        return $this->notificationRepository->findSubscriptionLatest($subscription, $limit);
    }

    public static function findRenewalNotificationByReminder(
        RenewalInterface  $renewal,
        ReminderInterface $reminder
    ): ?Notification {
        foreach ($renewal->getNotifications() as $notification) {
            if ($notification->getReminder() === $reminder) {
                return $notification;
            }
        }

        return null;
    }
}
