<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Ekyna\Bundle\SubscriptionBundle\Subscription\Provider\PriceProviderInterface;

/**
 * Interface PriceProviderSubjectInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface PriceProviderSubjectInterface
{
    /**
     * Sets the provider.
     *
     * @param PriceProviderInterface $provider
     */
    public function setPriceProvider(PriceProviderInterface $provider);
}