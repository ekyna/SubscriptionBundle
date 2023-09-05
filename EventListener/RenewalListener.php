<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Decimal\Decimal;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalUpdater;
use Ekyna\Bundle\SubscriptionBundle\Service\SaleItemUpdater;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function is_null;

/**
 * Class RenewalListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalListener
{
    public function __construct(
        private readonly PersistenceHelperInterface $persistenceHelper,
        private readonly RenewalUpdater             $renewalUpdater,
        private readonly SaleItemUpdater            $saleItemUpdater,
    ) {
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $renewal = $this->getRenewalFromEvent($event);

        if ($this->renewalUpdater->update($renewal)) {
            $this->persistenceHelper->persistAndRecompute($renewal, false);
        }

        $this->updateItemDescription($renewal);
        $this->updateItemNetPrice($renewal);

        if ($this->persistenceHelper->isScheduledForInsert($renewal->getSubscription())) {
            return;
        }

        $this->scheduleSubscriptionRenewalChange($renewal->getSubscription());
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $renewal = $this->getRenewalFromEvent($event);

        if ($this->renewalUpdater->update($renewal)) {
            $this->persistenceHelper->persistAndRecompute($renewal, false);
        }

        if ($this->persistenceHelper->isChanged($renewal, 'count')) {
            $this->updateItemQuantity($renewal);
        }

        if ($this->persistenceHelper->isChanged($renewal, ['startsAt', 'endsAt'])) {
            $this->updateItemNetPrice($renewal);
            $this->updateItemDescription($renewal);
        }

        if ($this->persistenceHelper->isChanged($renewal, ['startsAt', 'endsAt', 'paid'])) {
            $this->scheduleSubscriptionRenewalChange($renewal->getSubscription());
        }
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $renewal = $this->getRenewalFromEvent($event);

        if (null === $subscription = $renewal->getSubscription()) {
            $cs = $this->persistenceHelper->getChangeSet($renewal, 'subscription');
            if (empty($cs) || is_null($cs[0])) {
                throw new RuntimeException('Failed to retrieve subscription');
            }
            $subscription = $cs[0];
        }

        if ($this->persistenceHelper->isScheduledForRemove($subscription)) {
            return;
        }

        $this->scheduleSubscriptionRenewalChange($subscription);
    }

    protected function scheduleSubscriptionRenewalChange(SubscriptionInterface $subscription): void
    {
        $this->persistenceHelper->scheduleEvent($subscription, SubscriptionEvents::RENEWAL_CHANGE);
    }

    protected function updateItemQuantity(RenewalInterface $renewal): void
    {
        if (null === $item = $renewal->getOrderItem()) {
            return;
        }

        if (null === $order = $item->getRootSale()) {
            return;
        }

        if (OrderStates::STATE_NEW !== $order->getState()) {
            return;
        }

        $item->setQuantity(new Decimal($renewal->getCount()));

        $this->persistenceHelper->persistAndRecompute($item, true);
    }

    protected function updateItemNetPrice(RenewalInterface $renewal): void
    {
        if (null === $item = $renewal->getOrderItem()) {
            return;
        }

        if (null === $plan = $renewal->getSubscription()?->getPlan()) {
            throw new RuntimeException('Renewal subscription plan is not defined.');
        }

        if (null === $range = $renewal->getDateRange()) {
            throw new RuntimeException('Renewal date range is not defined.');
        }

        if ($this->saleItemUpdater->updateNetPrice($item, $plan, $range)) {
            $this->persistenceHelper->persistAndRecompute($item, false);
        }
    }

    protected function updateItemDescription(RenewalInterface $renewal): void
    {
        if (null === $item = $renewal->getOrderItem()) {
            return;
        }

        if (null === $range = $renewal->getDateRange()) {
            throw new RuntimeException('Renewal date range is not defined.');
        }

        if ($this->saleItemUpdater->updateDescription($item, $range)) {
            $this->persistenceHelper->persistAndRecompute($item, false);
        }
    }

    protected function getRenewalFromEvent(ResourceEventInterface $event): RenewalInterface
    {
        $renewal = $event->getResource();

        if (!$renewal instanceof RenewalInterface) {
            throw new UnexpectedTypeException($renewal, RenewalInterface::class);
        }

        return $renewal;
    }
}
