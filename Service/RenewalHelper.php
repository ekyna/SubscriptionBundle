<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Service\SaleItemHelper;
use Ekyna\Bundle\SubscriptionBundle\Exception\LogicException;
use Ekyna\Bundle\SubscriptionBundle\Factory\SubscriptionFactoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RenewalHelper
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalHelper
{
    public function __construct(
        private readonly SubscriptionFactoryInterface $subscriptionFactory,
        private readonly SaleFactoryInterface         $orderFactory,
        private readonly FactoryHelperInterface       $factoryHelper,
        private readonly SaleItemHelper               $saleItemHelper,
        private readonly TranslatorInterface          $translator,
    ) {
    }

    public function renew(RenewalInterface $renewal): OrderInterface
    {
        if (null !== $renewal->getOrderItem()) {
            throw new LogicException('Renewal is already linked to an order.');
        }

        if (null === $subscription = $renewal->getSubscription()) {
            throw new LogicException('Renewal subscription is not set');
        }

        if (null === $customer = $subscription->getCustomer()) {
            throw new LogicException('Subscription customer is not set');
        }

        if (null === $plan = $subscription->getPlan()) {
            throw new LogicException('Subscription plan is not set');
        }

        if (null === $product = $plan->getProduct()) {
            throw new LogicException('Plan product is not set');
        }

        if (null !== $forward = $plan->getForwardPlan()) {
            if (null === $product = $forward->getProduct()) {
                throw new LogicException('Plan product is not set');
            }

            $subscription->setState(SubscriptionStates::STATE_CANCELLED);

            $subscription = $this->subscriptionFactory->createWithCustomerAndPlan($customer, $forward);

            $renewal->setSubscription($subscription);
        }

        /** @var OrderInterface $order */
        $order = $this->orderFactory->createWithCustomer($customer);
        $order->setTitle($this->translator->trans('order.title', [], 'EkynaSubscription'));

        /** @var OrderItemInterface $item */
        $item = $this->factoryHelper->createItemForSale($order);
        $order->addItem($item);
        $renewal->setOrderItem($item);

        $event = new SaleItemEvent($item);
        $event->setDatum(RenewalInterface::DATA_KEY, $renewal);

        $this->saleItemHelper->initialize($item, $product, $event);

        $item->setQuantity(new Decimal($renewal->getCount()));

        $this->saleItemHelper->build($item, $event);

        return $order;
    }
}
