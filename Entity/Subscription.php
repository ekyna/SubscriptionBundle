<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

use function sprintf;

/**
 * Class Subscription
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Subscription extends AbstractResource implements SubscriptionInterface
{
    use TimestampableTrait;

    protected ?PlanInterface     $plan     = null;
    protected ?CustomerInterface $customer = null;
    protected string             $state     = SubscriptionStates::STATE_NEW;
    protected ?DateTimeInterface $expiresAt = null;
    /** @var Collection<RenewalInterface>|Selectable<RenewalInterface> */
    protected Collection $renewals;

    public function __construct()
    {
        $this->renewals = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->plan && $this->customer) {
            return sprintf('%s : %s', $this->plan, $this->customer);
        }

        return 'New subscription';
    }

    public function getPlan(): ?PlanInterface
    {
        return $this->plan;
    }

    public function setPlan(?PlanInterface $plan): SubscriptionInterface
    {
        $this->plan = $plan;

        return $this;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): SubscriptionInterface
    {
        $this->customer = $customer;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): SubscriptionInterface
    {
        $this->state = $state;

        return $this;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $date): SubscriptionInterface
    {
        $this->expiresAt = $date;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRenewals(): Collection
    {
        return $this->renewals;
    }

    public function hasRenewal(RenewalInterface $renewal): bool
    {
        return $this->renewals->contains($renewal);
    }

    public function addRenewal(RenewalInterface $renewal): SubscriptionInterface
    {
        if (!$this->hasRenewal($renewal)) {
            $this->renewals->add($renewal);
            $renewal->setSubscription($this);
        }

        return $this;
    }

    public function removeRenewal(RenewalInterface $renewal): SubscriptionInterface
    {
        if ($this->hasRenewal($renewal)) {
            $this->renewals->removeElement($renewal);
            $renewal->setSubscription(null);
        }

        return $this;
    }
}
