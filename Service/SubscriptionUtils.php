<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Exception\LogicException;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Common\Util\DateUtil;

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

        $renewals = self::filterRenewals($subscription, $renewal);

        $test = fn(RenewalInterface $r): bool => DateUtil::equals($r->getEndsAt(), $renewal->getEndsAt());

        return array_filter($renewals, $test);
    }

    /**
     * Returns the latest subscription renewal.
     */
    public static function findLatestRenewal(
        SubscriptionInterface $subscription,
        ?RenewalInterface     $ignore = null
    ): ?RenewalInterface {
        $renewals = self::getRenewals($subscription, $ignore);

        if (empty($renewals)) {
            return null;
        }

        return reset($renewals);
    }

    /**
     * Finds the renewal which is active at the given date.
     */
    public static function findActiveRenewalAt(
        SubscriptionInterface $subscription,
        DateTimeInterface     $date = null,
        ?RenewalInterface     $ignore = null
    ): ?RenewalInterface {
        $date = $date ?: new DateTime();

        $renewals = self::getRenewals($subscription, $ignore);

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
     * @return array<RenewalInterface>
     */
    public static function getRenewals(
        SubscriptionInterface $subscription,
        RenewalInterface      $ignore = null,
        bool                  $descendant = true
    ): array {
        if ($subscription->getRenewals()->isEmpty()) {
            return [];
        }

        $renewals = self::filterRenewals($subscription, $ignore);

        return self::sortRenewals($renewals, $descendant);
    }

    /**
     * @param array<RenewalInterface> $renewals
     *
     * @return array<RenewalInterface>
     */
    public static function sortRenewals(array $renewals, bool $descendant = true): array
    {
        if (empty($renewals)) {
            return [];
        }

        if ($descendant) {
            $callback = function (RenewalInterface $a, RenewalInterface $b) {
                $ret = $b->getStartsAt()->getTimestamp() - $a->getStartsAt()->getTimestamp();
                return $ret;
            };
        } else {
            $callback = function (RenewalInterface $a, RenewalInterface $b) {
                $ret = $a->getStartsAt()->getTimestamp() - $b->getStartsAt()->getTimestamp();
                return $ret;
            };
        }

        usort($renewals, $callback);

        return $renewals;
    }

    /**
     * @return array<RenewalInterface>
     *
     * @return array<RenewalInterface>
     */
    public static function filterRenewals(SubscriptionInterface $subscription, RenewalInterface $ignore = null): array
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
