<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

/**
 * Class PlanListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PlanListener
{
    public function onUpdate(): void
    {
        // TODO Prevent product change if any subscription use this plan
    }
}
