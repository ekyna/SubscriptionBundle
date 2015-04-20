<?php

namespace Ekyna\Bundle\SubscriptionBundle\Pricing\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\SubscriptionBundle\Entity\GroupPrice;
use Ekyna\Bundle\UserBundle\Entity\GroupRepository;

/**
 * Class GroupProvider
 * @package Ekyna\Bundle\SubscriptionBundle\Pricing\Provider
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GroupProvider implements ProviderInterface
{
    /**
     * @var GroupRepository
     */
    private $groupRepository;


    /**
     * Constructor.
     *
     * @param GroupRepository $groupRepository
     */
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function createPriceCollection()
    {
        $groups = $this->groupRepository->findAll();

        $prices = new ArrayCollection();
        foreach ($groups as $group) {
            $price = new GroupPrice();
            $price->setGroup($group);
            $prices->add($price);
        }

        return $prices;
    }
}
