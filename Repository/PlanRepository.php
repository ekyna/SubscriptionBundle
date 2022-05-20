<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\Hydrator\IdHydrator;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class PlanRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PlanRepository extends ResourceRepository implements PlanRepositoryInterface
{
    public function getIdentifiers(): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->select('p.id')
            ->getQuery()
            ->getResult(IdHydrator::class);
    }
}
