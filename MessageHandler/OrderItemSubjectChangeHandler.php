<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\MessageHandler;

use Ekyna\Bundle\SubscriptionBundle\Message\OrderItemSubjectChange;

/**
 * Class OrderItemSubjectChangeHandler
 * @package Ekyna\Bundle\SubscriptionBundle\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemSubjectChangeHandler
{
    public function __invoke(OrderItemSubjectChange $message): void
    {
        // TODO
    }
}
