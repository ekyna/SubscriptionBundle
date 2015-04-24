<?php

namespace Ekyna\Bundle\SubscriptionBundle\Event;

use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SubscriptionEvent
 * @package Ekyna\Bundle\SubscriptionBundle\Event
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionEvent extends Event
{
    /**
     * @var SubscriptionInterface
     */
    private $subscription;

    /**
     * Constructor.
     *
     * @param SubscriptionInterface $subscription
     */
    function __construct(SubscriptionInterface $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Returns the subscription.
     *
     * @return SubscriptionInterface
     */
    public function getSubscription()
    {
        return $this->subscription;
    }
}
