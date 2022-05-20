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

    private SubscriptionRepositoryInterface $repository;
    private ResourceManagerInterface        $manager;

    public function __construct(SubscriptionRepositoryInterface $repository, ResourceManagerInterface $manager)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->manager = $manager;
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
