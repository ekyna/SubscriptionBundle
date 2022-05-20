<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface PlanRepositoryInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface PlanRepositoryInterface extends ResourceRepositoryInterface
{
    public function getIdentifiers(): array;
}
