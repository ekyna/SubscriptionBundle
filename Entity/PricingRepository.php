<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Ekyna\Bundle\AdminBundle\Doctrine\ORM\ResourceRepository;
use Ekyna\Bundle\SubscriptionBundle\Subscription\Provider\PriceProviderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PriceProviderSubjectInterface;

/**
 * Class PricingRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PricingRepository extends ResourceRepository implements PriceProviderSubjectInterface
{
    /**
     * @var PriceProviderInterface
     */
    protected $priceProvider;

    /**
     * Sets the provider.
     *
     * @param PriceProviderInterface $provider
     */
    public function setPriceProvider(PriceProviderInterface $provider)
    {
        $this->priceProvider = $provider;
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
            ->setPrices($this->priceProvider->createPriceCollection())
        ;

        return $pricing;
    }
}
