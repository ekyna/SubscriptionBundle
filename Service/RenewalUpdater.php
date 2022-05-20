<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Model\DateRange;

use function sprintf;

/**
 * Class RenewalUpdater
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalUpdater
{
    public function update(RenewalInterface $renewal): bool
    {
        if (null === $subscription = $renewal->getSubscription()) {
            throw new RuntimeException('Renewal subscription is not set');
        }

        if (null === $subscription->getPlan()) {
            throw new RuntimeException('Subscription plan is not set');
        }

        $changed = false;

        if (null === $renewal->getStartsAt()) {
            $range = $this->calculateDateRange($renewal);

            $changed = $this->updateStartsAt($renewal, $range->getStart());

            $changed = $this->updateEndsAt($renewal, $range->getEnd()) || $changed;
        }

        if (0 === $renewal->getCount()) {
            $count = $this->calculateCount($renewal);

            $changed = $this->updateCount($renewal, $count) || $changed;
        }

        return $changed;
    }

    private function calculateDateRange(RenewalInterface $renewal): DateRange
    {
        if (null !== $sibling = SubscriptionUtils::findSibling($renewal)) {
            return $sibling->getRange();
        }

        $subscription = $renewal->getSubscription();

        // Select the initial start date.
        $start = new DateTime();
        if (null !== $date = $renewal->getStartsAt()) {
            $start = clone $date;
        } elseif (null !== $order = $renewal->getOrder()) {
            if (1 === $subscription->getRenewals()->count()) {
                $start = clone $order->getShippedAt();
            } else {
                $start = clone $order->getCreatedAt();
            }
        }

        $at = (clone $start)->modify('+1 month');
        if (null !== $extend = SubscriptionUtils::findActiveRenewalAt($subscription, $at, $renewal)) {
            return $extend->getRange();
        }

        $plan = $subscription->getPlan();

        $duration = $plan->getInitialDuration();
        if (null !== $latest = SubscriptionUtils::findLatest($subscription, $renewal)) {
            $start = (clone $latest->getEndsAt());
            $start->modify('+1 day');

            $duration = $plan->getRenewalDuration();
        }

        if (($item = $renewal->getOrderItem()) && $item->hasParent()) {
            $duration *= $item->getQuantity()->toInt();
        }

        $end = (clone $start);
        $end->modify(sprintf('+%d months', $duration));
        $end->modify('-1 day');

        return new DateRange($start, $end);
    }

    private function updateStartsAt(RenewalInterface $renewal, DateTimeInterface $dateTime): bool
    {
        if (DateUtil::equals($dateTime, $renewal->getStartsAt())) {
            return false;
        }

        $renewal
            ->setStartsAt($dateTime)
            ->setEndsAt(null);

        return true;
    }

    private function updateEndsAt(RenewalInterface $renewal, DateTimeInterface $dateTime): bool
    {
        if (DateUtil::equals($dateTime, $renewal->getEndsAt())) {
            return false;
        }

        $renewal->setEndsAt($dateTime);

        return true;
    }

    private function updateCount(RenewalInterface $renewal, int $count): bool
    {
        if ($count === $renewal->getCount()) {
            return false;
        }

        $renewal->setCount($count);

        return true;
    }

    private function calculateCount(RenewalInterface $renewal): int
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

        // Look for a previous renewal
        $latest = SubscriptionUtils::findLatest($renewal->getSubscription(), $renewal);
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
