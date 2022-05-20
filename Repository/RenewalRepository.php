<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class RenewalRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalRepository extends ResourceRepository implements RenewalRepositoryInterface
{
    public function findOneByOrderItemId(int $id): ?RenewalInterface
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->andWhere($qb->expr()->eq('IDENTITY(r.orderItem)', ':id'))
            ->setMaxResults(1)
            ->getQuery()
            ->setParameter('id', $id)
            ->getOneOrNullResult();
    }
}
