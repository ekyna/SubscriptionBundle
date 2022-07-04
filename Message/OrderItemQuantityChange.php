<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Message;

use Ekyna\Component\Commerce\Order\EventListener\OrderItemListener;

/**
 * Class OrderItemQuantityChange
 * @package Ekyna\Component\Commerce\Order\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * A message dispatched when order item quantity changes.
 * This message is not dispatched if the related order's state has change from/to stockable stable.
 */
class OrderItemQuantityChange
{
    private int    $orderItemId;
    private string $fromQuantity;
    private string $toQuantity;

    public function __construct(int $orderItemId, string $fromQuantity, string $toQuantity)
    {
        $this->orderItemId = $orderItemId;
        $this->fromQuantity = $fromQuantity;
        $this->toQuantity = $toQuantity;
    }

    public function getOrderItemId(): int
    {
        return $this->orderItemId;
    }

    public function getFromQuantity(): string
    {
        return $this->fromQuantity;
    }

    public function getToQuantity(): string
    {
        return $this->toQuantity;
    }
}
