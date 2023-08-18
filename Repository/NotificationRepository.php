<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\SubscriptionBundle\Entity\Notification;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;

/**
 * Class NotificationRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return array<int, Notification>
     */
    public function findSubscriptionLatest(SubscriptionInterface $subscription, int $limit = 3): array
    {
        $qb = $this->createQueryBuilder('n');

        return $qb
            ->join('n.renewal', 'r')
            ->where($qb->expr()->eq('r.subscription', ':subscription'))
            ->orderBy('n.notifiedAt', 'DESC')
            ->getQuery()
            ->setMaxResults($limit)
            ->setParameter('subscription', $subscription)
            ->getResult();
    }
}
