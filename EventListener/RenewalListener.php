<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalUpdater;
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
    private PersistenceHelperInterface $persistenceHelper;
    private RenewalUpdater             $renewalUpdater;

    public function __construct(PersistenceHelperInterface $persistenceHelper, RenewalUpdater $renewalUpdater)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->renewalUpdater = $renewalUpdater;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $renewal = $this->getRenewalFromEvent($event);

        if ($this->renewalUpdater->update($renewal)) {
            $this->persistenceHelper->persistAndRecompute($renewal, false);
        }

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

    protected function getRenewalFromEvent(ResourceEventInterface $event): RenewalInterface
    {
        $renewal = $event->getResource();

        if (!$renewal instanceof RenewalInterface) {
            throw new UnexpectedTypeException($renewal, RenewalInterface::class);
        }

        return $renewal;
    }
}
