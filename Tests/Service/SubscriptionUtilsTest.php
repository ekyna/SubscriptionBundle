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

use function array_reverse;
use function date;

/**
 * Class SubscriptionUtilsTest
 * @package Ekyna\Bundle\SubscriptionBundle\Tests\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionUtilsTest extends TestCase
{
    /**
     * @dataProvider provideSortRenewals
     */
    public function testSortRenewals(array $renewals, array $expected): void
    {
        $actual = SubscriptionUtils::sortRenewals($renewals, false);

        self::assertEquals($expected, $actual);

        $actual = SubscriptionUtils::sortRenewals($renewals);

        self::assertEquals(array_reverse($expected), $actual);
    }

    public function provideSortRenewals(): Generator
    {
        $expected = [
            $a = $this->createRenewal('2000-03-03', '2000-12-31'),
            $b = $this->createRenewal('2000-06-15', '2000-12-31'),
            $c = $this->createRenewal('2021-01-01', '2021-12-31'),
            $d = $this->createRenewal('2021-04-23', '2021-12-31'),
            $e = $this->createRenewal('2022-01-01', '2022-12-31'),
        ];

        yield [[$e, $c, $a, $d, $b], $expected];
        yield [[$c, $d, $b, $a, $e], $expected];
        yield [[$a, $d, $e, $b, $c], $expected];
    }

    /**
     * @dataProvider provideFindActiveRenewalAt
     */
    public function testFindActiveRenewalAt(
        SubscriptionInterface $subscription,
        ?RenewalInterface     $expected,
        ?DateTimeInterface    $date,
        ?RenewalInterface     $ignored
    ): void {
        $result = SubscriptionUtils::findActiveRenewalAt($subscription, $date, $ignored);

        self::assertSame($expected, $result);
    }

    public function provideFindActiveRenewalAt(): Generator
    {
        $subscription = new Subscription();

        yield [$subscription, null, null, null];

        $subscription = new Subscription();
        $subscription
            ->addRenewal(
                $this->createRenewal('2020-01-01', '2020-12-31')->setPaid(true)
            )
            ->addRenewal(
                $expected = $this->createRenewal('2021-01-01', '2021-12-31')->setPaid(true)
            )
            ->addRenewal(
                $this->createRenewal('2000-03-03', '2000-12-31')->setPaid(true)
            );

        yield [$subscription, $expected, new DateTime('2021-06-15'), null];

        $expected = null;
        $subscription = new Subscription();
        for ($i = -1; $i < 2; $i++) {
            $year = $i + (int)date('Y');

            $renewal = (new Renewal())
                ->setStartsAt(new DateTime($year.'-01-01'))
                ->setEndsAt(new DateTime($year.'-12-31'))
                ->setPaid(true);

            $subscription->addRenewal($renewal);

            if ($i === 0) {
                $expected = $renewal;
            }
        }

        yield [$subscription, $expected, null, null];

        $subscription = new Subscription();
        $subscription
            ->addRenewal(
                (new Renewal())
                    ->setStartsAt(new DateTime('2020-01-01'))
                    ->setEndsAt(new DateTime('2020-12-31'))
                    ->setPaid(true)
            )
            ->addRenewal(
                $ignore = (new Renewal())
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

        yield [$subscription, null, new DateTime('2021-06-15'), $ignore];
    }

    private function createRenewal(string $start, string $end): Renewal
    {
        return (new Renewal())
            ->setStartsAt(new DateTime($start))
            ->setEndsAt(new DateTime($end));
    }
}
