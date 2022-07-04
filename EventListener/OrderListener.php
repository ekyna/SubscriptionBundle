<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Ekyna\Bundle\SubscriptionBundle\Message\OrderStateChange;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Message\MessageQueueAwareTrait;
use Ekyna\Component\Resource\Persistence\PersistenceAwareTrait;

/**
 * Class OrderListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderListener
{
    use MessageQueueAwareTrait;
    use PersistenceAwareTrait;

    public function onStateChange(ResourceEventInterface $event): void
    {
        $order = $event->getResource();

        if (!$order instanceof OrderInterface) {
            throw new UnexpectedTypeException($order, OrderInterface::class);
        }

        $changeSet = $this->persistenceHelper->getChangeSet($order, [
            'state',
            'paymentState',
            'shipmentState',
            'invoiceState',
        ]);

        if (empty($changeSet)) {
            return;
        }

        $this->messageQueue->addMessage(static function () use ($order, $changeSet) {
            return OrderStateChange::create($order, $changeSet);
        });
    }
}
