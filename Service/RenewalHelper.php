<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Service\SaleItemHelper;
use Ekyna\Bundle\SubscriptionBundle\Exception\LogicException;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class RenewalHelper
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalHelper
{
    private SaleFactoryInterface   $orderFactory;
    private FactoryHelperInterface $factoryHelper;
    private SaleItemHelper         $saleItemHelper;

    public function __construct(
        SaleFactoryInterface   $orderFactory,
        FactoryHelperInterface $factoryHelper,
        SaleItemHelper         $saleItemHelper
    ) {
        $this->orderFactory = $orderFactory;
        $this->factoryHelper = $factoryHelper;
        $this->saleItemHelper = $saleItemHelper;
    }

    public function renew(RenewalInterface $renewal): OrderInterface
    {
        if (null !== $renewal->getOrderItem()) {
            throw new LogicException('Renewal is already linked to an order.');
        }

        if (null === $subscription = $renewal->getSubscription()) {
            throw new LogicException('Renewal subscription is not set');
        }

        /** @var OrderInterface $order */
        $order = $this->orderFactory->createWithCustomer($subscription->getCustomer());

        /** @var OrderItemInterface $item */
        $item = $this->factoryHelper->createItemForSale($order);
        $order->addItem($item);
        $renewal->setOrderItem($item);

        $subject = $subscription->getPlan()->getProduct();

        $this->saleItemHelper->initialize($item, $subject);

        $item->setQuantity(new Decimal($renewal->getCount()));

        $this->saleItemHelper->build($item);

        return $order;
    }
}
