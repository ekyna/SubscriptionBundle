<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Message;

/**
 * Class OrderItemAdd
 * @package Ekyna\Bundle\SubscriptionBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAdd
{
    private int    $orderItemId;
    private string $quantity;
    private ?string $subjectProvider;
    private ?int    $subjectIdentifier;

    public function __construct(int $orderItemId, string $quantity, ?string $subjectProvider, ?int $subjectIdentifier)
    {
        $this->orderItemId = $orderItemId;
        $this->quantity = $quantity;
        $this->subjectProvider = $subjectProvider;
        $this->subjectIdentifier = $subjectIdentifier;
    }

    public function getOrderItemId(): int
    {
        return $this->orderItemId;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function getSubjectProvider(): ?string
    {
        return $this->subjectProvider;
    }

    public function getSubjectIdentifier(): ?int
    {
        return $this->subjectIdentifier;
    }
}
