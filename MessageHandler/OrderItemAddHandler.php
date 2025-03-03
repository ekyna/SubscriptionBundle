<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\MessageHandler;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\SubscriptionBundle\Message\OrderItemAdd;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionGenerator;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class OrderItemAddHandler
 * @package Ekyna\Bundle\SubscriptionBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAddHandler
{
    public function __construct(
        private readonly ResourceRepositoryInterface $orderItemRepository,
        private readonly SubscriptionGenerator       $subscriptionGenerator,
        private readonly ResourceManagerInterface    $subscriptionManager
    ) {
    }

    public function __invoke(OrderItemAdd $message): void
    {
        /** @var OrderItemInterface $item */
        $item = $this->orderItemRepository->find($message->getOrderItemId());
        if (null === $item) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $item->getRootSale();

        $subscriptions = $this->subscriptionGenerator->generateFromOrder($order);

        foreach ($subscriptions as $subscription) {
            $this->subscriptionManager->persist($subscription);
        }

        $this->subscriptionManager->flush();
    }
}
