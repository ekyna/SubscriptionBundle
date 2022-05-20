<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Tests\Service;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\SubscriptionBundle\Entity\Renewal;
use Ekyna\Bundle\SubscriptionBundle\Entity\Subscription;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionUtils;
use Generator;
use PHPUnit\Framework\TestCase;

use function date;

/**
 * Class SubscriptionHelper
 * @package Ekyna\Bundle\SubscriptionBundle\Tests\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionHelperTest extends TestCase
{
    /**
     * @dataProvider provideFindActiveRenewalAt
     */
    public function testFindActiveRenewalAt(
        SubscriptionInterface $subscription,
        ?RenewalInterface     $expected,
        ?DateTimeInterface    $date
    ): void {
        $result = SubscriptionUtils::findActiveRenewalAt($subscription, $date);

        self::assertSame($expected, $result);
    }

    public function provideFindActiveRenewalAt(): Generator
    {
        $subscription = new Subscription();

        yield [$subscription, null, null];

        $subscription = new Subscription();
        $subscription
            ->addRenewal(
                (new Renewal())
                    ->setStartsAt(new DateTime('2020-01-01'))
                    ->setEndsAt(new DateTime('2020-12-31'))
                    ->setPaid(true)
            )
            ->addRenewal(
                $expected = (new Renewal())
                    ->setStartsAt(new DateTime('2021-01-01'))
                    ->setEndsAt(new DateTime('2021-12-31'))
                    ->setPaid(true)
            )
            ->addRenewal(
                (new Renewal())
                    ->setStartsAt(new DateTime('2022-01-01'))
                    ->setEndsAt(new DateTime('2022-12-31'))
                    ->setPaid(true)
            );

        yield [$subscription, $expected, new DateTime('2021-06-15')];

        $expected = null;
        $subscription = new Subscription();
        for ($i = -1; $i < 2; $i++) {
            $year = date('Y') + $i;

            $renewal = (new Renewal())
                ->setStartsAt(new DateTime($year.'-01-01'))
                ->setEndsAt(new DateTime($year.'-12-31'))
                ->setPaid(true);

            $subscription->addRenewal($renewal);

            if ($i === 0) {
                $expected = $renewal;
            }
        }

        yield [$subscription, $expected, null];
    }
}
