<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Ekyna\Bundle\SubscriptionBundle\Message\OrderItemAdd;
use Ekyna\Bundle\SubscriptionBundle\Message\OrderItemQuantityChange;
use Ekyna\Bundle\SubscriptionBundle\Message\OrderItemSubjectChange;
use Ekyna\Component\Commerce\Common\Helper\QuantityChangeHelper;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Message\MessageQueueAwareTrait;
use Ekyna\Component\Resource\Persistence\PersistenceAwareTrait;

use function array_fill;

/**
 * Class OrderItemListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemListener
{
    use MessageQueueAwareTrait;
    use PersistenceAwareTrait;

    private function getItemFromEvent(ResourceEventInterface $event): OrderItemInterface
    {
        $item = $event->getResource();

        if (!$item instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($item, OrderItemInterface::class);
        }

        return $item;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $item = $this->getItemFromEvent($event);

        // If order is not in stockable state
        if (!OrderStates::isStockableState($item->getRootSale()->getState())) {
            return;
        }

        $this->messageQueue->addMessage(function () use ($item) {
            $identity = $item->getSubjectIdentity();

            return new OrderItemAdd(
                $item->getId(),
                $item->getQuantity()->toFixed(5),
                $identity->getProvider(),
                $identity->getIdentifier()
            );
        });
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $item = $this->getItemFromEvent($event);

        if (!$this->persistenceHelper->isChanged($item, [
            'quantity',
            'subjectIdentity.provider',
            'subjectIdentity.identifier',
        ])) {
            return;
        }

        // Abort if sale is not in a stockable state
        $sale = $item->getRootSale();
        if (!OrderStates::isStockableState($sale->getState())) {
            return;
        }

        // Abort if order just changed to a stockable state
        $stateCs = $this->persistenceHelper->getChangeSet($sale, 'state');
        if (!empty($stateCs) && OrderStates::hasChangedToStockable($stateCs)) {
            return;
        }

        $this->sendMessagesRecursively($item);
    }

    /**
     * Applies the sale item to stock units recursively.
     */
    protected function sendMessagesRecursively(OrderItemInterface $item): void
    {
        // If subject has changed
        if ($this->persistenceHelper->isChanged($item, ['subjectIdentity.provider', 'subjectIdentity.identifier'])) {
            $this->enqueueSubjectChangeMessage($item);
        } elseif ($this->persistenceHelper->isChanged($item, 'quantity')) {
            $this->enqueueQuantityChangeMessage($item);
        } else {
            return;
        }

        foreach ($item->getChildren() as $child) {
            if ($this->persistenceHelper->isScheduledForInsert($child)) {
                continue;
            }

            if (
                $this->persistenceHelper->isScheduledForUpdate($child)
                && $this->persistenceHelper->isChanged($child, [
                    'quantity',
                    'subjectIdentity.provider',
                    'subjectIdentity.identifier',
                ])
            ) {
                // Skip this item as the listener will be called on it.
                /** @see OrderItemListener::onUpdate() */
                continue;
            }

            $this->sendMessagesRecursively($child);
        }
    }

    private function enqueueSubjectChangeMessage(OrderItemInterface $item): void
    {
        $providers = $this->persistenceHelper->getChangeSet($item, 'subjectIdentity.provider');
        $identifiers = $this->persistenceHelper->getChangeSet($item, 'subjectIdentity.identifier');

        if (empty($providers) && empty($identifiers)) {
            throw new LogicException('Unchanged order item subject.');
        }

        if (empty($providers)) {
            $providers = array_fill(0, 2, $item->getSubjectIdentity()->getProvider());
        }
        if (empty($identifiers)) {
            $identifiers = array_fill(0, 2, $item->getSubjectIdentity()->getIdentifier());
        }

        $helper = new QuantityChangeHelper($this->persistenceHelper);
        $quantities = $helper->getTotalQuantityChangeSet($item);
        if (empty($quantities)) {
            $quantities = array_fill(0, 2, $item->getTotalQuantity());
        }

        $message = new OrderItemSubjectChange($item->getId(), $quantities[0]->toFixed(5), $quantities[1]->toFixed(5));
        $message->setFromSubject($providers[0], $identifiers[0]);
        $message->setToSubject($providers[1], $identifiers[1]);

        $this->messageQueue->addMessage($message);
    }

    private function enqueueQuantityChangeMessage(OrderItemInterface $item): void
    {
        $helper = new QuantityChangeHelper($this->persistenceHelper);
        $quantities = $helper->getTotalQuantityChangeSet($item);

        if (empty($quantities)) {
            throw new LogicException('Unchanged order item quantities.');
        }

        $message = new OrderItemQuantityChange($item->getId(), $quantities[0]->toFixed(5), $quantities[1]->toFixed(5));

        $this->messageQueue->addMessage($message);
    }
}
