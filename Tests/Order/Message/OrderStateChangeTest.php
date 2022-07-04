<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Tests\Order\Message;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderStateChangeTest
 * @package Ekyna\Component\Commerce\Tests\Order\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderStateChangeTest extends TestCase
{
    public function testCreate(): void
    {
        $order = static::createMock(OrderInterface::class);

        $order
            ->expects(static::any())
            ->method('getId')
            ->willReturn(1);

        $order
            ->expects(static::any())
            ->method('getState')
            ->willReturn(OrderStates::STATE_NEW);

        $order
            ->expects(static::any())
            ->method('getPaymentState')
            ->willReturn(PaymentStates::STATE_NEW);

        $order
            ->expects(static::any())
            ->method('getShipmentState')
            ->willReturn(ShipmentStates::STATE_NEW);

        $order
            ->expects(static::any())
            ->method('getInvoiceState')
            ->willReturn(InvoiceStates::STATE_NEW);

        $message = \Ekyna\Bundle\SubscriptionBundle\Message\OrderStateChange::create($order, [
            'paymentState' => [
                null,
                PaymentStates::STATE_NEW,
            ],
            'shipmentState' => [
                null,
                ShipmentStates::STATE_SHIPPED,
            ],
            'invoiceState' => [
                InvoiceStates::STATE_CANCELED,
                InvoiceStates::STATE_COMPLETED,
            ],
        ]);

        self::assertSame(OrderStates::STATE_NEW, $message->getFromState());
        self::assertSame(OrderStates::STATE_NEW, $message->getToState());

        self::assertSame(PaymentStates::STATE_NEW, $message->getFromPaymentState());
        self::assertSame(PaymentStates::STATE_NEW, $message->getToPaymentState());

        self::assertSame(ShipmentStates::STATE_NEW, $message->getFromShipmentState());
        self::assertSame(ShipmentStates::STATE_SHIPPED, $message->getToShipmentState());

        self::assertSame(InvoiceStates::STATE_CANCELED, $message->getFromInvoiceState());
        self::assertSame(InvoiceStates::STATE_COMPLETED, $message->getToInvoiceState());
    }
}
