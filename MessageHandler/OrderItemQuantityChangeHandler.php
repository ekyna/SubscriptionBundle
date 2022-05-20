<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\MessageHandler;

use Ekyna\Bundle\SubscriptionBundle\Repository\RenewalRepositoryInterface;
use Ekyna\Component\Commerce\Order\Message\OrderItemQuantityChange;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Class OrderItemQuantityChangeHandler
 * @package Ekyna\Bundle\SubscriptionBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemQuantityChangeHandler
{
    private RenewalRepositoryInterface $repository;
    private ResourceManagerInterface   $manager;

    public function __construct(RenewalRepositoryInterface $repository, ResourceManagerInterface $manager)
    {
        $this->repository = $repository;
        $this->manager = $manager;
    }

    public function __invoke(OrderItemQuantityChange $message): void
    {
        $renewal = $this->repository->findOneByOrderItemId($message->getOrderItemId());
        if (null === $renewal) {
            return;
        }

        $count = (int)$message->getToQuantity();

        if ($renewal->getCount() === $count) {
            return;
        }

        $renewal->setCount($count);

        $this->manager->persist($count);
        $this->manager->flush();
    }
}
