<?php

namespace Ekyna\Bundle\SubscriptionBundle\Subscription\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\SubscriptionBundle\Util\Year;
use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Class GroupPriceProvider
 * @package Ekyna\Bundle\SubscriptionBundle\Subscription\Provider
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GroupPriceProvider extends AbstractPriceProvider
{
    /**
     * @var string
     */
    protected $groupClass;


    /**
     * Sets the group class.
     *
     * @param string $class
     */
    public function setGroupClass($class)
    {
        $this->groupClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function createPriceCollection()
    {
        $groups = $this->em->getRepository($this->groupClass)->findAll();

        $prices = new ArrayCollection();
        foreach ($groups as $group) {
            /** @var \Ekyna\Bundle\SubscriptionBundle\Entity\GroupPrice $price */
            $price = new $this->priceClass();
            $price->setGroup($group);
            $prices->add($price);
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    public function findPriceByUserAndYear(UserInterface $user, $year)
    {
        $year = Year::validate($year);

        $findPriceByUserAndYearDql = <<<DQL
SELECT p FROM %s p
JOIN p.pricing y
WHERE y.year = :year
AND p.group = :group
DQL;

        $dql = sprintf($findPriceByUserAndYearDql, $this->priceClass);

        return $this->em
            ->createQuery($dql)
            ->setMaxResults(1)
            ->setParameter('year', $year)
            ->setParameter('group', $user->getGroup())
            ->getOneOrNullResult()
        ;
    }
}
