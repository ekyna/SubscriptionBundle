<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\DateRange;

use function date;
use function sprintf;

/**
 * Class DateRangeCalculator
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalCalculator
{
    public function calculateDateRange(RenewalInterface $renewal): DateRange
    {
        if (null !== $sibling = SubscriptionUtils::findSibling($renewal)) {
            return $sibling->getRange();
        }

        if (null === $subscription = $renewal->getSubscription()) {
            throw new RuntimeException('Renewal subscription is not set');
        }

        // Select the initial start date.
        $start = new DateTime();
        if (null !== $date = $renewal->getStartsAt()) {
            // Keep defined start date
            $start = clone $date;
        } elseif (null !== $order = $renewal->getOrder()) {
            // Renewal is linked to an order
            if (1 === $subscription->getRenewals()->count()) {
                // First renewal: use «shipped at» if available date
                $start = clone ($order->getShippedAt() ?? $order->getCreatedAt());
            } else {
                $start = clone $order->getCreatedAt();
            }
        }

        // If another renewal of this subscription is active 1 month later
        $at = (clone $start)->modify('+1 month');
        if (null !== $extend = SubscriptionUtils::findActiveRenewalAt($subscription, $at, $renewal)) {
            // Use its date range
            return $extend->getRange();
        }

        if (null === $plan = $subscription->getPlan()) {
            throw new RuntimeException('Subscription plan is not set');
        }

        $duration = $plan->getInitialDuration();
        if (null !== $latest = SubscriptionUtils::findLatestRenewal($subscription, $renewal)) {
            $start = (clone $latest->getEndsAt());
            $start->modify('+1 day');

            $duration = $plan->getRenewalDuration();
        }

        // Use plan anniversary
        if (null !== $date = $plan->getRenewalDate()) {
            $year = (int)date('Y');
            while ($start > $end = $date->toDate($year)->modify('-1 day')) {
                $year++;
            }

            return new DateRange($start, $end);
        }

        // Use plan duration
        if (($item = $renewal->getOrderItem()) && $item->hasParent()) {
            $duration *= $item->getQuantity()->toInt();
        }

        $end = (clone $start);
        $end->modify(sprintf('+%d months', $duration));
        $end->modify('-1 day');

        return new DateRange($start, $end);
    }

    public function calculateDateRangeWithPlan(PlanInterface $plan, DateTimeInterface $start = null): DateRange
    {
        $range = new DateRange($start);

        if ($date = $plan->getRenewalDate()) {
            $year = (int)date('Y');
            while ($start > $end = $date->toDate($year)->modify('-1 day')) {
                $year++;
            }
        } else {
            $end = (clone $start)->modify("+{$plan->getInitialDuration()} month")->modify('-1 day');
        }

        $range->setEnd($end);

        return $range;
    }

    public function calculateCount(RenewalInterface $renewal): int
    {
        // If renewal is linked to an order item
        if (null !== $item = $renewal->getOrderItem()) {
            // If item has a parent
            if ($item->hasParent()) {
                // Use the parent total quantity (item quantity is used to multiply duration)
                $quantity = $item->getParent()->getTotalQuantity();
            } else {
                $quantity = $item->getTotalQuantity();
            }

            return $quantity->toInt();
        }

        if (null === $subscription = $renewal->getSubscription()) {
            throw new RuntimeException('Renewal subscription is not set');
        }

        // Look for a previous renewal
        $latest = SubscriptionUtils::findLatestRenewal($subscription, $renewal);
        if (null === $latest) {
            return 1;
        }

        $count = $latest->getCount();

        foreach (SubscriptionUtils::findExtensions($latest) as $extension) {
            $count += $extension->getCount();
        }

        return $count;
    }
}
