<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SubscriptionRepositoryInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<SubscriptionInterface>
 */
interface SubscriptionRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @param PlanInterface     $plan
     * @param CustomerInterface $customer
     *
     * @return SubscriptionInterface|null
     */
    public function findOneByPlanAndCustomer(PlanInterface $plan, CustomerInterface $customer): ?SubscriptionInterface;

    /**
     * @param OrderInterface $order
     *
     * @return array<int, SubscriptionInterface>
     */
    public function findByOrder(OrderInterface $order): array;

    /**
     * @return array<int, SubscriptionInterface>
     */
    public function findExpiringToday(): array;

    /**
     * Finds subscription to remind, ie:
     * - Not canceled.
     * - With 'auto notify' enabled.
     * - Same plan as given reminder.
     * - That will expire in $reminder->days days.
     * - Not having paid renewal posterior to current renewal.
     * - Having pending renewal not notified for the given reminder.
     *
     * @param ReminderInterface $reminder
     *
     * @return array<int, SubscriptionInterface>
     */
    public function findToRemind(ReminderInterface $reminder): array;
}
