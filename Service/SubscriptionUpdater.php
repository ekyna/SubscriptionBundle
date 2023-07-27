<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Commerce\Common\Util\DateUtil;

/**
 * Class SubscriptionUpdater
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionUpdater
{
    private SubscriptionStateResolver $stateResolver;

    public function __construct(SubscriptionStateResolver $stateResolver)
    {
        $this->stateResolver = $stateResolver;
    }

    public function update(SubscriptionInterface $subscription): bool
    {
        $changed = $this->updateState($subscription);

        return $this->updateExpiresAt($subscription) || $changed;
    }

    private function updateState(SubscriptionInterface $subscription): bool
    {
        $state = $this->stateResolver->resolve($subscription);

        if ($state === $subscription->getState()) {
            return false;
        }

        $subscription->setState($state);

        return true;
    }

    private function updateExpiresAt(SubscriptionInterface $subscription): bool
    {
        $expiresAt = $this->resolveExpiresAt($subscription);

        if (DateUtil::equals($expiresAt, $subscription->getExpiresAt())) {
            return false;
        }

        $subscription->setExpiresAt($expiresAt);

        return true;
    }

    private function resolveExpiresAt(SubscriptionInterface $subscription): ?DateTimeInterface
    {
        if (SubscriptionStates::STATE_CANCELLED === $subscription->getState()) {
            return null;
        }

        if (null === $latest = SubscriptionUtils::findLatestRenewal($subscription)) {
            return null;
        }

        return (clone $latest->getEndsAt())->modify('+1 day');
    }
}
