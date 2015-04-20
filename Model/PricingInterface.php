<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface PricingInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface PricingInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set year
     *
     * @param string $year
     * @return PricingInterface|$this
     */
    public function setYear($year);

    /**
     * Get year
     *
     * @return string
     */
    public function getYear();

    /**
     * Sets the prices.
     *
     * @param ArrayCollection|PriceInterface[] $prices
     * @return PricingInterface|$this
     */
    public function setPrices(ArrayCollection $prices);

    /**
     * Returns whether the gris has the price or not.
     *
     * @param PriceInterface $price
     * @return bool
     */
    public function hasPrice(PriceInterface $price);

    /**
     * Adds the price.
     *
     * @param PriceInterface $price
     * @return PricingInterface|$this
     */
    public function addPrice(PriceInterface $price);

    /**
     * Removes the price.
     *
     * @param PriceInterface $price
     * @return PricingInterface|$this
     */
    public function removePrice(PriceInterface $price);

    /**
     * Returns the prices.
     *
     * @return ArrayCollection|PriceInterface[]
     */
    public function getPrices();
}
