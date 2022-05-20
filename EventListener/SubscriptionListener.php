<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionUpdater;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class SubscriptionListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionListener
{
    private SubscriptionUpdater        $subscriptionUpdater;
    private PersistenceHelperInterface $persistenceHelper;

    public function __construct(SubscriptionUpdater $subscriptionUpdater, PersistenceHelperInterface $persistenceHelper)
    {
        $this->subscriptionUpdater = $subscriptionUpdater;
        $this->persistenceHelper = $persistenceHelper;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $subscription = $this->getSubscriptionFromEvent($event);

        $this->update($subscription);
    }

    public function onRenewalChange(ResourceEventInterface $event): void
    {
        $subscription = $this->getSubscriptionFromEvent($event);

        $this->update($subscription);
    }

    protected function update(SubscriptionInterface $subscription): void
    {
        if (!$this->subscriptionUpdater->update($subscription)) {
            return;
        }

        $this->persistenceHelper->persistAndRecompute($subscription, false);
    }

    protected function getSubscriptionFromEvent(ResourceEventInterface $event): SubscriptionInterface
    {
        $subscription = $event->getResource();

        if (!$subscription instanceof SubscriptionInterface) {
            throw new UnexpectedTypeException($subscription, SubscriptionInterface::class);
        }

        return $subscription;
    }
}
