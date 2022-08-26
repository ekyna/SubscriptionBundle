<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class Subscribe
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Subscribe
{
    private ?CustomerInterface $customer = null;
    private ?PlanInterface     $plan     = null;

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): Subscribe
    {
        $this->customer = $customer;

        return $this;
    }

    public function getPlan(): ?PlanInterface
    {
        return $this->plan;
    }

    public function setPlan(?PlanInterface $plan): Subscribe
    {
        $this->plan = $plan;

        return $this;
    }
}
