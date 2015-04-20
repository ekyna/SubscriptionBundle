<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PriceInterface;

/**
 * Class Pricing
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Pricing implements PricingInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $year;

    /**
     * @var ArrayCollection|PriceInterface[]
     */
    protected $prices;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getYear();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrices(ArrayCollection $prices)
    {
        /** @var PriceInterface $price */
        foreach ($prices as $price) {
            $price->setPricing($this);
        }
        $this->prices = $prices;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPrice(PriceInterface $price)
    {
        return $this->prices->contains($price);
    }

    /**
     * {@inheritdoc}
     */
    public function addPrice(PriceInterface $price)
    {
        if (!$this->hasPrice($price)) {
            $price->setPricing($this);
            $this->prices->add($price);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePrice(PriceInterface $price)
    {
        if ($this->hasPrice($price)) {
            $price->setPricing(null);
            $this->prices->removeElement($price);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrices()
    {
        return $this->prices;
    }
}
