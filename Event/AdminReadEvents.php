<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Event;

/**
 * Class AdminReadEvents
 * @package Ekyna\Bundle\SubscriptionBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class AdminReadEvents
{
    public const PLAN         = 'ekyna_subscription.plan.admin_read';
    public const SUBSCRIPTION = 'ekyna_subscription.subscription.admin_read';
}
