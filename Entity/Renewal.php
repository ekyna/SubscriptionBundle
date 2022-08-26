<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\DateRange;

use function sprintf;

/**
 * Class Renewal
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Renewal extends AbstractResource implements RenewalInterface
{
    private ?SubscriptionInterface $subscription = null;
    private ?OrderItemInterface    $orderItem    = null;
    private ?DateTimeInterface     $startsAt     = null;
    private ?DateTimeInterface     $endsAt       = null;
    private int                    $count        = 0;
    private bool                   $needsReview  = false;
    private bool                   $paid         = false;
    private DateTimeInterface      $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function __toString(): string
    {
        if ($this->startsAt && $this->endsAt) {
            return sprintf('%s - %s', $this->startsAt->format('Y-m-d'), $this->endsAt->format('Y-m-d'));
        }

        return 'New renewal';
    }

    public function getSubscription(): ?SubscriptionInterface
    {
        return $this->subscription;
    }

    public function setSubscription(?SubscriptionInterface $subscription): RenewalInterface
    {
        if ($this->subscription === $subscription) {
            return $this;
        }

        if ($previous = $this->subscription) {
            $this->subscription = null;
            $previous->removeRenewal($this);
        }

        if ($this->subscription = $subscription) {
            $this->subscription = $subscription;
            $this->subscription->addRenewal($this);
        }

        return $this;
    }

    public function getOrderItem(): ?OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItemInterface $item): RenewalInterface
    {
        $this->orderItem = $item;

        return $this;
    }

    public function getStartsAt(): ?DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(?DateTimeInterface $date): RenewalInterface
    {
        $this->startsAt = $date;

        return $this;
    }

    public function getEndsAt(): ?DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?DateTimeInterface $date): RenewalInterface
    {
        $this->endsAt = $date;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): RenewalInterface
    {
        $this->count = $count;

        return $this;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): RenewalInterface
    {
        $this->paid = $paid;

        return $this;
    }

    public function isNeedsReview(): bool
    {
        return $this->needsReview;
    }

    public function setNeedsReview(bool $needsReview): RenewalInterface
    {
        $this->needsReview = $needsReview;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): RenewalInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->orderItem?->getRootSale();
    }

    public function getRange(): DateRange
    {
        return new DateRange(clone $this->startsAt, clone $this->endsAt);
    }
}
