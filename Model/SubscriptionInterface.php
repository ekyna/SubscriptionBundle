<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\SubscriptionBundle\Entity\Subscription;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface SubscriptionInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubscriptionInterface extends ResourceInterface, TimestampableInterface
{
    public function getPlan(): ?PlanInterface;

    public function setPlan(?PlanInterface $plan): SubscriptionInterface;

    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): SubscriptionInterface;

    public function getState(): string;

    public function setState(string $state): SubscriptionInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): SubscriptionInterface;

    public function isAutoNotify(): bool;

    public function setAutoNotify(bool $autoNotify): SubscriptionInterface;

    public function getExpiresAt(): ?DateTimeInterface;

    public function setExpiresAt(?DateTimeInterface $date): SubscriptionInterface;

    /**
     * @return Collection<RenewalInterface>
     */
    public function getRenewals(): Collection;

    public function hasRenewal(RenewalInterface $renewal): bool;

    public function addRenewal(RenewalInterface $renewal): SubscriptionInterface;

    public function removeRenewal(RenewalInterface $renewal): SubscriptionInterface;
}
