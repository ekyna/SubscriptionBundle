<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use DateTimeInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface RenewalInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface RenewalInterface extends ResourceInterface
{
    public function getSubscription(): ?SubscriptionInterface;

    public function setSubscription(?SubscriptionInterface $subscription): RenewalInterface;

    public function getOrderItem(): ?OrderItemInterface;

    public function setOrderItem(?OrderItemInterface $item): RenewalInterface;

    public function getStartsAt(): ?DateTimeInterface;

    public function setStartsAt(?DateTimeInterface $date): RenewalInterface;

    public function getEndsAt(): ?DateTimeInterface;

    public function setEndsAt(?DateTimeInterface $date): RenewalInterface;

    public function getCount(): int;

    public function setCount(int $count): RenewalInterface;

    public function isPaid(): bool;

    public function setPaid(bool $paid): RenewalInterface;

    public function isNeedsReview(): bool;

    public function setNeedsReview(bool $needsReview): RenewalInterface;

    public function getCreatedAt(): DateTimeInterface;

    public function setCreatedAt(DateTimeInterface $createdAt): RenewalInterface;

    public function getOrder(): ?OrderInterface;

    public function getRange(): DateRange;
}