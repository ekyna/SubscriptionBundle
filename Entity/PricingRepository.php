<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Ekyna\Bundle\AdminBundle\Doctrine\ORM\ResourceRepository;
use Ekyna\Bundle\SubscriptionBundle\Pricing\Provider\ProviderInterface;

/**
 * Class PricingRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PricingRepository extends ResourceRepository
{
    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * Sets the provider.
     *
     * @param ProviderInterface $provider
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Creates a new pricing.
     *
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface
     */
    public function createNew()
    {
        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface $pricing */
        $pricing = parent::createNew();

        $pricing
            ->setYear(date('Y'))
            ->setPrices($this->provider->createPriceCollection())
        ;

        return $pricing;
    }
}
