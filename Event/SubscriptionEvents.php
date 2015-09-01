<?php

namespace Ekyna\Bundle\SubscriptionBundle\Event;

/**
 * Class SubscriptionEvents
 * @package Ekyna\Bundle\SubscriptionBundle\Event
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionEvents
{
    const PRE_GENERATE  = 'ekyna_subscription.subscription.pre_generate';
    const POST_GENERATE = 'ekyna_subscription.subscription.post_generate';

    const STATE_CHANGED = 'ekyna_subscription.subscription.state_changed';
}
