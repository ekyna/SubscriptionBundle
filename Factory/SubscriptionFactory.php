<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Factory;

use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

/**
 * Class SubscriptionFactory
 * @package Ekyna\Bundle\SubscriptionBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionFactory extends ResourceFactory implements SubscriptionFactoryInterface
{
    public function createWithCustomerAndPlan(CustomerInterface $customer, PlanInterface $plan): SubscriptionInterface
    {
        $subscription = parent::create();

        if (!$subscription instanceof SubscriptionInterface) {
            throw new UnexpectedTypeException($subscription, SubscriptionInterface::class);
        }

        $subscription
            ->setCustomer($customer)
            ->setPlan($plan);

        return $subscription;
    }
}
