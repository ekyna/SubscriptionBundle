<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Component\Commerce\Common\Util\DateUtil;

/**
 * Class RenewalUpdater
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalUpdater
{
    public function __construct(
        private readonly RenewalCalculator $renewalCalculator
    ) {
    }

    public function update(RenewalInterface $renewal): bool
    {
        $changed = false;

        if (null === $renewal->getStartsAt()) {
            $range = $this->renewalCalculator->calculateDateRange($renewal);

            $changed = $this->updateStartsAt($renewal, $range->getStart());

            $changed = $this->updateEndsAt($renewal, $range->getEnd()) || $changed;
        }

        if (0 === $renewal->getCount()) {
            $count = $this->renewalCalculator->calculateCount($renewal);

            $changed = $this->updateCount($renewal, $count) || $changed;
        }

        return $changed;
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
}
