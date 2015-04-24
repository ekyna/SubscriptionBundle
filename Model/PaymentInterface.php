<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Sale\Payment\PaymentInterface as BaseInterface;

/**
 * Interface PaymentInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface PaymentInterface extends BaseInterface
{
    /**
     * Returns whether the subscription has the subscription.
     *
     * @param SubscriptionInterface $subscription
     * @return bool
     */
    public function hasSubscription(SubscriptionInterface $subscription);

    /**
     * Adds the subscription.
     *
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInterface|$this
     */
    public function addSubscription(SubscriptionInterface $subscription);

    /**
     * Removes the subscription.
     *
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInterface|$this
     */
    public function removeSubscription(SubscriptionInterface $subscription);

    /**
     * Returns the subscriptions.
     *
     * @return ArrayCollection|SubscriptionInterface[]
     */
    public function getSubscriptions();
}
