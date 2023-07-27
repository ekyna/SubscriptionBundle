<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WatchSubscriptionCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class WatchSubscriptionCommand extends Command
{
    protected static $defaultName = 'ekyna:subscription:watch';

    public function __construct(
        private readonly SubscriptionRepositoryInterface $repository,
        private readonly ResourceManagerInterface $manager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subscriptions = $this->repository->findExpiringToday();

        foreach ($subscriptions as $subscription) {
            $subscription->setState(SubscriptionStates::STATE_EXPIRED);

            $this->manager->persist($subscription);
        }

        $this->manager->flush();

        return Command::SUCCESS;
    }
}
