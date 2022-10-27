<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use DateTimeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Model\Anniversary;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PlanInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface PlanInterface extends ResourceInterface
{
    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): PlanInterface;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): PlanInterface;

    /**
     * Returns the initial duration in months.
     */
    public function getInitialDuration(): int;

    /**
     * Sets the initial duration in months.
     */
    public function setInitialDuration(int $duration): PlanInterface;

    /**
     * Returns the renewal duration in months.
     */
    public function getRenewalDuration(): int;

    /**
     * Sets the renewal duration in months.
     */
    public function setRenewalDuration(int $duration): PlanInterface;

    /**
     * Returns the plan that should be used to renew.
     */
    public function getForwardPlan(): ?PlanInterface;

    /**
     * Sets the plan that should be used to renew.
     */
    public function setForwardPlan(?PlanInterface $forwardPlan): PlanInterface;

    /**
     * Returns the renewal «anniversary» date.
     */
    public function getRenewalDate(): ?Anniversary;

    /**
     * Sets the renewal «anniversary» date.
     */
    public function setRenewalDate(?Anniversary $anniversary): PlanInterface;

    /**
     * Returns the date when subscription should be renewed.
     */
    public function getAnniversary(): ?DateTimeInterface;
}
