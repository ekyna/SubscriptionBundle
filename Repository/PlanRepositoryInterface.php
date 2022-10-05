<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface PlanRepositoryInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<PlanInterface>
 */
interface PlanRepositoryInterface extends ResourceRepositoryInterface
{
    public function getIdentifiers(): array;

    /**
     * @return iterable<PlanInterface>
     */
    public function findByProduct(ProductInterface $product, int $limit = null): iterable;
}
