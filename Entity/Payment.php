<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\PaymentBundle\Entity\Payment as BasePayment;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PaymentInterface;

/**
 * Class SubscriptionPayment
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Payment extends BasePayment implements PaymentInterface
{
    /**
     * @var ArrayCollection|SubscriptionInterface[]
     */
    protected $subscriptions;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->subscriptions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSubscription(SubscriptionInterface $subscription)
    {
        return $this->subscriptions->contains($subscription);
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscription(SubscriptionInterface $subscription)
    {
        if (!$this->hasSubscription($subscription)) {
            $subscription->addPayment($this);
            $this->subscriptions->add($subscription);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSubscription(SubscriptionInterface $subscription)
    {
        if ($this->hasSubscription($subscription)) {
            $subscription->removePayment($this);
            $this->subscriptions->removeElement($subscription);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }
}
