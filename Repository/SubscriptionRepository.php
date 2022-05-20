<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class SubscriptionRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionRepository extends ResourceRepository implements SubscriptionRepositoryInterface
{
    public function findOneByPlanAndCustomer(PlanInterface $plan, CustomerInterface $customer): ?SubscriptionInterface
    {
        return $this->findOneBy([
            'plan'     => $plan,
            'customer' => $customer,
        ]);
    }

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
     * @return array<SubscriptionInterface>
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
}
