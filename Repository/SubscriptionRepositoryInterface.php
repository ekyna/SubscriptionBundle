<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SubscriptionRepositoryInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubscriptionRepositoryInterface extends ResourceRepositoryInterface
{
    public function findOneByPlanAndCustomer(PlanInterface $plan, CustomerInterface $customer): ?SubscriptionInterface;

    /**
     * @return array<SubscriptionInterface>
     */
    public function findByOrder(OrderInterface $order): array;

    /**
     * @return array<SubscriptionInterface>
     */
    public function findExpiringToday(): array;
}
