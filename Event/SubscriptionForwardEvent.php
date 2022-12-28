<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Event;

use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SubscriptionForwardEvent
 * @package Ekyna\Bundle\SubscriptionBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionForwardEvent extends Event
{
    public function __construct(
        public readonly SubscriptionInterface $previous,
        public readonly SubscriptionInterface $current,
    ) {
    }
}
