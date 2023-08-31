<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

use function sprintf;

/**
 * Class SubscriptionRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionRepository extends ResourceRepository implements SubscriptionRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByPlanAndCustomer(PlanInterface $plan, CustomerInterface $customer): ?SubscriptionInterface
    {
        return $this->findOneBy([
            'plan'     => $plan,
            'customer' => $customer,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findByOrder(OrderInterface $order): array
    {
        $qb = $this->createQueryBuilder('s');

        return $qb
            ->join('s.renewals', 'r')
            ->join('r.orderItem', 'i1')
            ->leftJoin('i1.parent', 'i2')
            ->leftJoin('i2.parent', 'i3')
            ->leftJoin('i3.parent', 'i4')
            ->leftJoin('i4.parent', 'i5')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('i1.order', ':order'),
                $qb->expr()->eq('i2.order', ':order'),
                $qb->expr()->eq('i3.order', ':order'),
                $qb->expr()->eq('i4.order', ':order'),
                $qb->expr()->eq('i5.order', ':order')
            ))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('order', $order)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findExpiringToday(): array
    {
        $qb = $this->createQueryBuilder('s');

        return $qb
            ->andWhere($qb->expr()->lte('s.expiresAt', ':date'))
            ->andWhere($qb->expr()->notIn('s.state', ':states'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('date', new DateTime(), Types::DATE_MUTABLE)
            ->setParameter('states', [
                SubscriptionStates::STATE_CANCELLED,
                SubscriptionStates::STATE_EXPIRED,
                SubscriptionStates::STATE_NEW,
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findToRemind(ReminderInterface $reminder, DateTimeInterface $date = null): array
    {
        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        $date = ($date ?? new DateTime())->modify(
            sprintf('+%d days', $reminder->getDays())
        )->format('Y-m-d');

        return $qb
            // Same plan as given reminder.
            ->andWhere($ex->eq('s.plan', ':plan'))
            // Not canceled.
            ->andWhere($ex->neq('s.state', $ex->literal(SubscriptionStates::STATE_CANCELLED)))
            // With 'auto notify' enabled
            ->andWhere($ex->eq('s.autoNotify', 1))
            // That will expire in '$reminder->days' days.
            ->andWhere($ex->eq('DATE(s.expiresAt)', ':date'))
            // Not having paid renewal posterior to current renewal.
            ->leftJoin('s.renewals', 'r', Join::WITH, 'r.startsAt >= s.expiresAt')
            ->andWhere($ex->orX(
                $ex->isNull('r'),
                $ex->eq('r.paid', 0),
            ))
            // Having pending renewal not notified for the given reminder.
            ->leftJoin('r.notifications', 'n', Join::WITH, 'n.reminder = :reminder')
            ->andWhere($ex->isNull('n'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('plan', $reminder->getPlan())
            ->setParameter('date', $date)
            ->setParameter('reminder', $reminder)
            ->getResult();
    }
}
