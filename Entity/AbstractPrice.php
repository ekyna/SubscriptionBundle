<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PriceInterface;

/**
 * Class Price
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPrice implements PriceInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var PricingInterface
     */
    protected $pricing;

    /**
     * @var float
     */
    protected $amount;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Returns the
     * @return string
     */
    abstract public function getName();

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
    public function setPricing(PricingInterface $pricing = null)
    {
        $this->pricing = $pricing;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPricing()
    {
        return $this->pricing;
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
