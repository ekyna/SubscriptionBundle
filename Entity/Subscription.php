<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\SubscriptionBundle\Model\PaymentInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PriceInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Class Subscription
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Subscription implements SubscriptionInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var PriceInterface
     */
    protected $price;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var ArrayCollection|PaymentInterface[]
     */
    protected $payments;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = SubscriptionStates::PENDING;
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
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice(PriceInterface $price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        if ($state === SubscriptionStates::PENDING && $this->state === SubscriptionStates::PAID) {
            throw new \RuntimeException('Can\'t change state from "paid" to "pending".');
        }
        $this->state = $state;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPayment(PaymentInterface $payment)
    {
        return $this->payments->contains($payment);
    }

    /**
     * {@inheritdoc}
     */
    public function addPayment(PaymentInterface $payment)
    {
        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePayment(PaymentInterface $payment)
    {
        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
        }
        return $this;
    }

    /**
     * Returns the payments.
     *
     * @return ArrayCollection|PaymentInterface[]
     */
    public function getPayments()
    {
        return $this->payments;
    }
}