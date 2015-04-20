<?php

namespace Ekyna\Bundle\SubscriptionBundle\Pricing\Provider;

/**
 * Interface ProviderInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Pricing\Provider
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ProviderInterface
{
    /**
     * Creates a price collection.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function createPriceCollection();
}
