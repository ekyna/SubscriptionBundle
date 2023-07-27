<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\MessageHandler;

use Ekyna\Bundle\SubscriptionBundle\Message\Notify;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\ReminderRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\Notifier;

/**
 * Class NotifyHandler
 * @package Ekyna\Bundle\SubscriptionBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyHandler
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly ReminderRepositoryInterface $reminderRepository,
        private readonly Notifier $notifier,
    ) {
    }

    public function __invoke(Notify $message): void
    {
        $subscription = $this->subscriptionRepository->find($message->subscriptionId);
        if (!$subscription instanceof SubscriptionInterface) {
            return;
        }

        $reminder = $this->reminderRepository->find($message->reminderId);
        if (!$reminder instanceof ReminderInterface) {
            return;
        }

        $this->notifier->remind($subscription, $reminder);
    }
}
