<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Message;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class OrderStateChangeMessage
 * @package Ekyna\Bundle\SubscriptionBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderStateChange
{
    public static function create(OrderInterface $order, array $changeSet): self
    {
        $data = [];

        foreach (self::DEFAULTS as $key => $value) {
            $data[$key] = [
                $changeSet[$key][0] ?? $value,
                $changeSet[$key][1] ?? $value,
            ];
        }

        $data[self::SAMPLE] = $order->isSample();

        return new self($order->getId(), $data);
    }

    private const GENERAL  = 'state';
    private const PAYMENT  = 'paymentState';
    private const SHIPMENT = 'shipmentState';
    private const INVOICE  = 'invoiceState';
    private const SAMPLE   = 'sample';

    private const DEFAULTS = [
        self::GENERAL  => OrderStates::STATE_NEW,
        self::PAYMENT  => PaymentStates::STATE_NEW,
        self::SHIPMENT => ShipmentStates::STATE_NEW,
        self::INVOICE  => InvoiceStates::STATE_NEW,
    ];

    private int   $orderId;
    private array $data;

    private function __construct(int $orderId, array $data)
    {
        $this->orderId = $orderId;
        $this->data = $data;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getFromState(): string
    {
        return $this->data[self::GENERAL][0];
    }

    public function getToState(): string
    {
        return $this->data[self::GENERAL][1];
    }

    public function getFromPaymentState(): string
    {
        return $this->data[self::PAYMENT][0];
    }

    public function getToPaymentState(): string
    {
        return $this->data[self::PAYMENT][1];
    }

    public function getFromShipmentState(): string
    {
        return $this->data[self::SHIPMENT][0];
    }

    public function getToShipmentState(): string
    {
        return $this->data[self::SHIPMENT][1];
    }

    public function getFromInvoiceState(): string
    {
        return $this->data[self::INVOICE][0];
    }

    public function getToInvoiceState(): string
    {
        return $this->data[self::INVOICE][1];
    }

    public function isSample(): bool
    {
        return $this->data[self::SAMPLE];
    }
}
