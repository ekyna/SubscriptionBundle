<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    protected ?SubscriptionInterface $subscription = null;
    protected ?OrderItemInterface    $orderItem    = null;
    protected ?DateTimeInterface     $startsAt     = null;
    protected ?DateTimeInterface     $endsAt       = null;
    protected int                    $count        = 0;
    protected bool                   $needsReview  = false;
    protected bool                   $paid         = false;
    protected DateTimeInterface      $createdAt;

    protected Collection $notifications;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->notifications = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->count && $this->endsAt) {
            return sprintf('[%d] ⇾ %s', $this->count, $this->endsAt->format('Y-m-d'));
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

    public function getDateRange(): ?DateRange
    {
        if (null !== $this->startsAt && null !== $this->endsAt) {
            return new DateRange($this->startsAt, $this->endsAt);
        }

        return null;
    }

    public function setDateRange(DateRange $range): RenewalInterface
    {
        $this->startsAt = DateTime::createFromInterface($range->getStart());
        $this->endsAt = DateTime::createFromInterface($range->getEnd());

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

    public function addNotification(Notification $notification): RenewalInterface
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setRenewal($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): RenewalInterface
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            $notification->setRenewal(null);
        }

        return $this;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
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
