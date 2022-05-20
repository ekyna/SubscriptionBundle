<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Event;

/**
 * Class PlanEvents
 * @package Ekyna\Bundle\SubscriptionBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class PlanEvents
{
    public const INSERT         = 'ekyna_subscription.plan.insert';
    public const UPDATE         = 'ekyna_subscription.plan.update';
    public const DELETE         = 'ekyna_subscription.plan.delete';

    public const PRE_CREATE     = 'ekyna_subscription.plan.pre_create';
    public const POST_CREATE    = 'ekyna_subscription.plan.post_create';

    public const PRE_UPDATE     = 'ekyna_subscription.plan.pre_update';
    public const POST_UPDATE    = 'ekyna_subscription.plan.post_update';

    public const PRE_DELETE     = 'ekyna_subscription.plan.pre_delete';
    public const POST_DELETE    = 'ekyna_subscription.plan.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
