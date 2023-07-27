<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Message;

/**
 * Class Notify
 * @package Ekyna\Bundle\SubscriptionBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Notify
{
    public function __construct(
        public int $subscriptionId,
        public int $reminderId,
    ) {
    }
}
