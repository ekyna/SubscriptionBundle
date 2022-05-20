<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Exception\LogicException;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Resource\Model\DateRange;
use Generator;

use function array_fill;
use function array_filter;
use function uasort;

/**
 * Class SubscriptionUtils
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionUtils
{
    public static function isLocked(SubscriptionInterface $subscription): bool
    {
        foreach ($subscription->getRenewals() as $renewal) {
            if (null !== $renewal->getOrderItem()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Finds the renewal's sibling (ie renewal linked to the same order).
     */
    public static function findSibling(RenewalInterface $renewal): ?RenewalInterface
    {
        if (null === $subscription = $renewal->getSubscription()) {
            throw new LogicException('Renewal subscription is not set.');
        }

        if (null === $order = $renewal->getOrder()) {
            return null;
        }

        foreach ($subscription->getRenewals() as $r) {
            if ($renewal === $r) {
                continue;
            }

            if ($order !== $r->getOrder()) {
                continue;
            }

            return $r;
        }

        return null;
    }

    /**
     * Return renewals which are active (ie paid) during the same period as the given renewal.
     *
     * @return array<RenewalInterface>
     */
    public static function findExtensions(RenewalInterface $renewal): array
    {
        if (null === $subscription = $renewal->getSubscription()) {
            throw new LogicException('Renewal subscription is not set.');
        }

        $renewals = self::getRenewals($subscription, $renewal);

        $test = fn(RenewalInterface $r): bool => DateUtil::equals($r->getEndsAt(), $renewal->getEndsAt());

        return array_filter($renewals, $test);
    }

    /**
     * Finds the renewal's siblings (ie renewal linked to the same order).
     *
     * @param RenewalInterface $renewal
     * @param int|null         $limit
     *
     * @return Generator<RenewalInterface>
     *
     * @deprecated
     */
    public static function findSiblings(RenewalInterface $renewal, int $limit = null): Generator
    {
        if (null === $subscription = $renewal->getSubscription()) {
            throw new LogicException('Renewal subscription is not set.');
        }

        if (null === $order = $renewal->getOrder()) {
            return null;
        }

        $found = 0;
        foreach ($subscription->getRenewals() as $r) {
            if ($renewal === $r) {
                continue;
            }

            if ($order !== $r->getOrder()) {
                continue;
            }

            yield $r;

            $found++;
            if ((null !== $limit) && ($limit <= $found)) {
                break;
            }
        }
    }

    /**
     * @deprecated
     */
    public static function sortRenewalsOld(
        SubscriptionInterface $subscription,
        RenewalInterface      $ignore = null,
        bool                  $paid = true
    ): array {
        if ($subscription->getRenewals()->isEmpty()) {
            return [];
        }

        /** @var array<RenewalInterface> $renewals */
        $renewals = $subscription->getRenewals()->toArray();

        if ($ignore) {
            $renewals = array_filter($renewals, fn(RenewalInterface $r): bool => $r !== $ignore);
        }
        if ($paid) {
            $renewals = array_filter($renewals, fn(RenewalInterface $r): bool => $r->isPaid());
        }

        if (empty($renewals)) {
            return [];
        }

        // Sort by date desc
        uasort($renewals, function (RenewalInterface $a, RenewalInterface $b) {
            $aDate = $a->getStartsAt();
            $bDate = $b->getStartsAt();

            if (DateUtil::equals($aDate, $bDate)) {
                $aDate = $a->getEndsAt();
                $bDate = $b->getEndsAt();
            }

            return $bDate->getTimestamp() <=> $aDate->getTimestamp();
        });

        return $renewals;
    }

    /**
     * @deprecated
     */
    public static function latestRenewal(
        SubscriptionInterface $subscription,
        RenewalInterface      $ignore = null,
        bool                  $paid = true
    ): ?RenewalInterface {
        $renewals = self::sortRenewals($subscription, $ignore, $paid);

        if (empty($renewals)) {
            return null;
        }

        if ($ignore && ($order = $ignore->getOrder())) {
            foreach ($renewals as $renewal) {
                if ($order === $renewal->getOrder()) {
                    continue;
                }

                return $renewal;
            }
        }

        return reset($renewals);
    }

    public static function findLatest(
        SubscriptionInterface $subscription,
        ?RenewalInterface     $ignore = null
    ): ?RenewalInterface {
        $renewals = self::sortRenewals($subscription, $ignore);

        if (empty($renewals)) {
            return null;
        }

        return reset($renewals);
    }

    public static function findActiveRenewalAt(
        SubscriptionInterface $subscription,
        DateTimeInterface     $date = null,
        ?RenewalInterface     $ignore = null
    ): ?RenewalInterface {
        $date = $date ?: new DateTime();

        $renewals = self::sortRenewals($subscription, $ignore);

        foreach ($renewals as $renewal) {
            if ($date < $renewal->getStartsAt()) {
                continue;
            }

            if ($date > $renewal->getEndsAt()) {
                continue;
            }

            return $renewal;
        }

        return null;
    }

    /**
     * @deprecated
     */
    public static function findActiveInRange(
        SubscriptionInterface $subscription,
        DateRange             $range,
        ?RenewalInterface     $ignore = null
    ): ?RenewalInterface {
        $renewals = self::sortRenewals($subscription, $ignore, false);

        foreach ($renewals as $renewal) {
            if ($range->getEnd() < $renewal->getStartsAt()) {
                continue;
            }

            if ($range->getStart() > $renewal->getEndsAt()) {
                continue;
            }

            return $renewal;
        }

        return null;
    }

    public static function sortRenewals(
        SubscriptionInterface $subscription,
        ?RenewalInterface     $ignore = null,
        bool                  $descendant = true
    ): array {
        if ($subscription->getRenewals()->isEmpty()) {
            return [];
        }

        $renewals = self::getRenewals($subscription, $ignore);

        if ($descendant) {
            $callback = function (RenewalInterface $a, RenewalInterface $b) {
                return $b->getStartsAt()->getTimestamp() <=> $a->getStartsAt()->getTimestamp();
            };
        } else {
            $callback = function (RenewalInterface $a, RenewalInterface $b) {
                return $a->getStartsAt()->getTimestamp() <=> $b->getStartsAt()->getTimestamp();
            };
        }

        uasort($renewals, $callback);

        return $renewals;
    }

    /**
     * @return array<RenewalInterface>
     */
    private static function getRenewals(SubscriptionInterface $subscription, ?RenewalInterface $ignore = null): array
    {
        if ($subscription->getRenewals()->isEmpty()) {
            return [];
        }

        $renewals = $subscription->getRenewals()->toArray();

        // Paid filter
        $renewals = array_filter($renewals, fn(RenewalInterface $r): bool => $r->isPaid());

        // Ignore filter
        if (null !== $ignore) {
            $renewals = array_filter($renewals, fn(RenewalInterface $r): bool => $r !== $ignore);
        }

        return $renewals;
    }
}
