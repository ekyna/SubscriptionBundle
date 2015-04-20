<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;

/**
 * Interface PriceInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface PriceInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Returns the price name.
     *
     * @return mixed
     */
    public function getName();

    /**
     * Sets the pricing.
     *
     * @param PricingInterface $pricing
     * @return PriceInterface|$this
     */
    public function setPricing(PricingInterface $pricing = null);

    /**
     * Returns the pricing.
     *
     * @return PricingInterface
     */
    public function getPricing();

    /**
     * Set amount
     *
     * @param float $amount
     * @return PriceInterface|$this
     */
    public function setAmount($amount);

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount();
}
