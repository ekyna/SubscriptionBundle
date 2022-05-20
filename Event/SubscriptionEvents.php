<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Event;

/**
 * Class SubscriptionEvents
 * @package Ekyna\Bundle\SubscriptionBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionEvents
{
    public const INSERT         = 'ekyna_subscription.subscription.insert';
    public const UPDATE         = 'ekyna_subscription.subscription.update';
    public const DELETE         = 'ekyna_subscription.subscription.delete';

    public const PRE_CREATE     = 'ekyna_subscription.subscription.pre_create';
    public const POST_CREATE    = 'ekyna_subscription.subscription.post_create';

    public const PRE_UPDATE     = 'ekyna_subscription.subscription.pre_update';
    public const POST_UPDATE    = 'ekyna_subscription.subscription.post_update';

    public const PRE_DELETE     = 'ekyna_subscription.subscription.pre_delete';
    public const POST_DELETE    = 'ekyna_subscription.subscription.post_delete';

    public const RENEWAL_CHANGE = 'ekyna_subscription.subscription.renewals_change';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
