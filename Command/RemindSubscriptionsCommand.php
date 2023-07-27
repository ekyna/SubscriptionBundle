<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Ekyna\Bundle\SubscriptionBundle\Message\Notify;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\ReminderRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function sprintf;

/**
 * Class RemindSubscriptionsCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RemindSubscriptionsCommand extends Command
{
    protected static $defaultName = 'ekyna:subscription:remind';

    public function __construct(
        private readonly ReminderRepositoryInterface     $reminderRepository,
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly MessageBusInterface             $messageBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reminders = $this->reminderRepository->findEnabled();

        foreach ($reminders as $reminder) {
            $output->writeln(sprintf(
                "%s [%d]\n  - <comment>%s</comment>",
                $reminder->getPlan()->getDesignation(),
                $reminder->getDays(),
                $reminder->getTitle()
            ));

            $subscriptions = $this->subscriptionRepository->findToRemind($reminder);

            foreach ($subscriptions as $subscription) {
                $output->writeln(sprintf('%d : %s', $subscription->getId(), $subscription));

                $this->messageBus->dispatch(new Notify(
                    $subscription->getId(),
                    $reminder->getId()
                ));
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
