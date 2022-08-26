<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Factory;

use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;

/**
 * Interface SubscriptionFactoryInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubscriptionFactoryInterface extends ResourceFactoryInterface
{
    public function createWithCustomerAndPlan(CustomerInterface $customer, PlanInterface $plan): SubscriptionInterface;
}
