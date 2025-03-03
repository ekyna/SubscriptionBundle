<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Hydrator\IdHydrator;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class PlanRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PlanRepository extends ResourceRepository implements PlanRepositoryInterface
{
    public const IDENTIFIERS_CACHE_KEY = 'plans_identifiers';

    private ?array $planIdentifiers = null;

    public function getIdentifiers(): array
    {
        if (null !== $this->planIdentifiers) {
            return $this->planIdentifiers;
        }

        $qb = $this->createQueryBuilder('p');

        return $this->planIdentifiers = $qb
            ->select('IDENTITY(p.product)')
            ->getQuery()
            ->enableResultCache(3600 * 24, self::IDENTIFIERS_CACHE_KEY)
            ->getResult(IdHydrator::NAME);
    }

    public function findOneByProduct(ProductInterface $product): ?PlanInterface
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere($qb->expr()->eq('p.product', ':product'))
            ->getQuery()
            ->setParameter('product', $product)
            ->getOneOrNullResult();
    }
}
