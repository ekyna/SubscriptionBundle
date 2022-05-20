<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface RenewalRepositoryInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface RenewalRepositoryInterface extends ResourceRepositoryInterface
{
    public function findOneByOrderItemId(int $id): ?RenewalInterface;
}
