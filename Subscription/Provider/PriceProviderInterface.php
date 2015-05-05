<?php

namespace Ekyna\Bundle\SubscriptionBundle\Subscription\Provider;

use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Interface ProviderInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Subscription\Provider
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface PriceProviderInterface
{
    /**
     * Creates a price collection.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function createPriceCollection();

    /**
     * Finds the price for the given user and year.
     *
     * @param UserInterface $user
     * @param string        $year
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\PriceInterface|null
     */
    public function findPriceByUserAndYear(UserInterface $user, $year);
}
