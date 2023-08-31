<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use DateTime;
use DateTimeImmutable;
use Ekyna\Bundle\SubscriptionBundle\Message\Notify;
use Ekyna\Bundle\SubscriptionBundle\Repository\ReminderRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

use function array_unique;
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
        private readonly ReminderRepositoryInterface $reminderRepository,
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'The reference date');
        $this->addOption('modifier', 'm', InputOption::VALUE_REQUIRED, 'The reference date modifier');
        $this->addOption('id',
            'i',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'The subscription id to process (ignoring others)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $date = $input->getOption('from')) {
            try {
                $date = new DateTimeImmutable($date);
                if (null !== $modifier = $input->getOption('modifier')) {
                    if (false === $date = $date->modify($modifier)) {
                        throw new Exception();
                    }
                }
            } catch (Throwable) {
                $output->writeln('Failed to parse reference date');
            }
        }

        $reminders = $this->reminderRepository->findEnabled();

        if (empty($ids = $input->getOption('id'))) {
            $ids = null;
        } else {
            $ids = array_unique(array_map(static fn($v): int => (int)$v, $ids));
        }

        foreach ($reminders as $reminder) {
            $output->writeln(sprintf(
                "%s [%d]\n  - <comment>%s</comment>",
                $reminder->getPlan()->getDesignation(),
                $reminder->getDays(),
                $reminder->getTitle()
            ));

            $subscriptions = $this->subscriptionRepository->findToRemind($reminder, $date);

            foreach ($subscriptions as $subscription) {
                if (!empty($ids) && !in_array($subscription->getId(), $ids, true)) {
                    continue;
                }

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
