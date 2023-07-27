<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Ekyna\Bundle\SubscriptionBundle\Entity\Notification;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;

/**
 * Class ReminderHelper
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReminderHelper
{
    public static function findNotification(RenewalInterface $renewal, ReminderInterface $reminder): ?Notification
    {
        foreach ($renewal->getNotifications() as $notification) {
            if ($notification->getReminder() === $reminder) {
                return $notification;
            }
        }

        return null;
    }
}
