<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Event;

/**
 * Class RenewalEvents
 * @package Ekyna\Bundle\SubscriptionBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class RenewalEvents
{
    public const INSERT         = 'ekyna_subscription.renewal.insert';
    public const UPDATE         = 'ekyna_subscription.renewal.update';
    public const DELETE         = 'ekyna_subscription.renewal.delete';

    public const PRE_CREATE     = 'ekyna_subscription.renewal.pre_create';
    public const POST_CREATE    = 'ekyna_subscription.renewal.post_create';

    public const PRE_UPDATE     = 'ekyna_subscription.renewal.pre_update';
    public const POST_UPDATE    = 'ekyna_subscription.renewal.post_update';

    public const PRE_DELETE     = 'ekyna_subscription.renewal.pre_delete';
    public const POST_DELETE    = 'ekyna_subscription.renewal.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
