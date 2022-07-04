<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\MessageHandler;

use Ekyna\Bundle\SubscriptionBundle\Message\OrderStateChange;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionGenerator;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

use function in_array;

/**
 * Class OrderStateChangeHandler
 * @package Ekyna\Bundle\SubscriptionBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderStateChangeHandler
{
    private OrderRepositoryInterface $orderRepository;
    private SubscriptionGenerator    $subscriptionGenerator;
    private ResourceManagerInterface $subscriptionManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SubscriptionGenerator    $subscriptionGenerator,
        ResourceManagerInterface $subscriptionManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->subscriptionGenerator = $subscriptionGenerator;
        $this->subscriptionManager = $subscriptionManager;
    }

    public function __invoke(OrderStateChange $message): void
    {
        if (!$this->filterTransition($message)) {
            return;
        }

        $order = $this->orderRepository->findOneById($message->getOrderId());
        if (null === $order) {
            return;
        }

        $subscriptions = $this->subscriptionGenerator->generateFromOrder($order);

        foreach ($subscriptions as $subscription) {
            $this->subscriptionManager->persist($subscription);
        }

        $this->subscriptionManager->flush();
    }

    /**
     * Returns whether to handle this message.
     */
    private function filterTransition(OrderStateChange $message): bool
    {
        if ($message->isSample()) {
            return false;
        }

        $from = in_array($message->getFromPaymentState(), SubscriptionGenerator::RENEW_PAYMENT_STATES, true);
        $to = in_array($message->getToPaymentState(), SubscriptionGenerator::RENEW_PAYMENT_STATES, true);

        if ($from xor $to) {
            return true;
        }

        $from = in_array($message->getFromShipmentState(), SubscriptionGenerator::RENEW_SHIPMENT_STATES, true);
        $to = in_array($message->getToShipmentState(), SubscriptionGenerator::RENEW_SHIPMENT_STATES, true);

        return $from xor $to;
    }
}
