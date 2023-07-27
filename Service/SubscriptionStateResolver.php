<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;

/**
 * Class SubscriptionStateResolver
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionStateResolver
{
    public function resolve(SubscriptionInterface $subscription): string
    {
        if ($subscription->getState() === SubscriptionStates::STATE_CANCELLED) {
            return SubscriptionStates::STATE_CANCELLED;
        }

        $renewals = $subscription->getRenewals();

        if ($renewals->isEmpty()) {
            return SubscriptionStates::STATE_NEW;
        }

        // Latest paid renew
        if (null !== $renewal = SubscriptionUtils::findLatestRenewal($subscription)) {
            $today = (new DateTime())->setTime(23, 59, 59);
            if ($today > $renewal->getEndsAt()) {
                return SubscriptionStates::STATE_EXPIRED;
            }

            return SubscriptionStates::STATE_RENEWED;
        }

        return SubscriptionStates::STATE_PENDING;
    }
}
