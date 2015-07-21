<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Ekyna\Bundle\UserBundle\Model\UserInterface;

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
     * Sets the notifiedAt.
     *
     * @param \DateTime $notifiedAt
     * @return SubscriptionInterface|$this
     */
    public function setNotifiedAt(\DateTime $notifiedAt = null);

    /**
     * Returns the notifiedAt.
     *
     * @return \DateTime
     */
    public function getNotifiedAt();
}
