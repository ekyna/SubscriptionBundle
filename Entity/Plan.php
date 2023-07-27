<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\Anniversary;

/**
 * Class Plan
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Plan extends AbstractResource implements PlanInterface
{
    protected ?string           $designation     = null;
    protected ?ProductInterface $product         = null;
    protected int               $initialDuration = 12;
    protected int               $renewalDuration = 12;
    protected ?Anniversary      $renewalDate     = null;
    protected ?PlanInterface    $forwardPlan     = null;

    protected Collection $reminders;

    public function __construct()
    {
        $this->reminders = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->designation;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): PlanInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): PlanInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getInitialDuration(): int
    {
        return $this->initialDuration;
    }

    public function setInitialDuration(int $duration): PlanInterface
    {
        $this->initialDuration = $duration;

        return $this;
    }

    public function getRenewalDuration(): int
    {
        return $this->renewalDuration;
    }

    public function setRenewalDuration(int $duration): PlanInterface
    {
        $this->renewalDuration = $duration;

        return $this;
    }

    public function getForwardPlan(): ?PlanInterface
    {
        return $this->forwardPlan;
    }

    public function setForwardPlan(?PlanInterface $forwardPlan): PlanInterface
    {
        $this->forwardPlan = $forwardPlan;

        return $this;
    }

    public function getRenewalDate(): ?Anniversary
    {
        return $this->renewalDate;
    }

    public function setRenewalDate(?Anniversary $anniversary): PlanInterface
    {
        $this->renewalDate = $anniversary;

        return $this;
    }

    public function addReminder(ReminderInterface $reminder): PlanInterface
    {
        if (!$this->reminders->contains($reminder)) {
            $this->reminders->add($reminder);
        }

        return $this;
    }

    public function removeReminder(ReminderInterface $reminder): PlanInterface
    {
        if ($this->reminders->contains($reminder)) {
            $this->reminders->removeElement($reminder);
        }

        return $this;
    }

    public function getReminders(): Collection
    {
        return $this->reminders;
    }

    public function getAnniversary(): ?DateTimeInterface
    {
        return null;
    }
}
