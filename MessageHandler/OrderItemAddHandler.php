<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\MessageHandler;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Bundle\SubscriptionBundle\Message\OrderItemAdd;
use Ekyna\Bundle\SubscriptionBundle\Repository\PlanRepositoryInterface;
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
    private ResourceRepositoryInterface $orderItemRepository;
    private PlanRepositoryInterface     $planRepository;
    private SubscriptionGenerator       $subscriptionGenerator;
    private ResourceManagerInterface    $subscriptionManager;

    public function __construct(
        ResourceRepositoryInterface $orderItemRepository,
        PlanRepositoryInterface     $planRepository,
        SubscriptionGenerator       $subscriptionGenerator,
        ResourceManagerInterface    $subscriptionManager
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->planRepository = $planRepository;
        $this->subscriptionGenerator = $subscriptionGenerator;
        $this->subscriptionManager = $subscriptionManager;
    }

    public function __invoke(OrderItemAdd $message): void
    {
        /** @var OrderItemInterface $item */
        $item = $this->orderItemRepository->find($message->getOrderItemId());
        if (null === $item) {
            return;
        }

        if (!$this->itemHasPlanSubject($item)) {
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

    private function itemHasPlanSubject(OrderItemInterface $item): bool
    {
        if (!$item->hasSubjectIdentity()) {
            return false;
        }

        if ($item->getSubjectIdentity()->getProvider() !== ProductProvider::getName()) {
            return false;
        }

        return in_array(
            $item->getSubjectIdentity()->getProvider(),
            $this->planRepository->getIdentifiers(),
            true
        );
    }
}
