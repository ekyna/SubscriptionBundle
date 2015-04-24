<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface SubscriptionInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface SubscriptionInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     * @return SubscriptionInterface|$this
     */
    public function setUser(UserInterface $user);

    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser();

    /**
     * Sets the price.
     *
     * @param PriceInterface $price
     * @return SubscriptionInterface|$this
     */
    public function setPrice(PriceInterface $price);

    /**
     * Returns the price.
     *
     * @return PriceInterface
     */
    public function getPrice();

    /**
     * Sets the state.
     *
     * @param string $state
     * @return SubscriptionInterface|$this
     */
    public function setState($state);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Has the payment.
     *
     * @param PaymentInterface $payment
     * @return SubscriptionInterface|$this
     */
    public function hasPayment(PaymentInterface $payment);

    /**
     * Adds the payment.
     *
     * @param PaymentInterface $payment
     * @return SubscriptionInterface|$this
     */
    public function addPayment(PaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param PaymentInterface $payment
     * @return SubscriptionInterface|$this
     */
    public function removePayment(PaymentInterface $payment);

    /**
     * Returns the payments.
     *
     * @return ArrayCollection|PaymentInterface[]
     */
    public function getPayments();
}
