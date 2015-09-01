<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\SubscriptionBundle\Util\Year;
use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Class SubscriptionRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionRepository extends EntityRepository
{
    /**
     * Finds subscriptions by pricing.
     *
     * @param PricingInterface $pricing
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface[]
     */
    public function findByPricing(PricingInterface $pricing)
    {
        $qb = $this->createQueryBuilder('s');

        $parameters = array('pricing' => $pricing);

        $qb
            ->join('s.user', 'user')
            ->join('s.price', 'price')
            ->andWhere($qb->expr()->eq('price.pricing', ':pricing'))
            ->addOrderBy('user.lastName', 'ASC')
            ->addOrderBy('user.firstName', 'ASC')
        ;

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult()
        ;
    }

    /**
     * Finds subscriptions by user.
     *
     * @param UserInterface $user
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface[]
     */
    public function findByUser(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('s');

        $parameters = array('user' => $user);

        $qb
            ->join('s.price', 'price')
            ->join('price.pricing', 'pricing')
            ->andWhere($qb->expr()->eq('s.user', ':user'))
            ->addOrderBy('pricing.year', 'DESC')
        ;

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult()
        ;
    }

    /**
     * Finds the subscription by user an year.
     *
     * @param UserInterface $user
     * @param string        $year
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface|null
     */
    public function findOneByUserAndYear(UserInterface $user, $year)
    {
        Year::validate($year);

        $qb = $this->createQueryBuilder('s');

        $parameters = array('user' => $user, 'year' => $year);

        $qb
            ->join('s.price', 'price')
            ->join('price.pricing', 'pricing')
            ->andWhere($qb->expr()->eq('s.user', ':user'))
            ->andWhere($qb->expr()->eq('pricing.year', ':year'))
        ;

        return $qb
            ->getQuery()
            ->setMaxResults(1)
            ->setParameters($parameters)
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns subscriptions which require a payment by user.
     *
     * @param UserInterface $user
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface[]
     */
    public function findByUserAndPaymentRequired(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->join('s.price', 'price')
            ->join('price.pricing', 'pricing')
            ->andWhere($qb->expr()->eq('s.user', ':user'))
            ->andWhere($qb->expr()->eq('s.state', ':state'))
            ->addOrderBy('pricing.year', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('state', SubscriptionStates::STATE_NEW)
        ;

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns whether the user has subscriptions that requires a payment.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function userHasPaymentRequiredSubscriptions(UserInterface $user)
    {
        $s = $this->findByUserAndPaymentRequired($user);

        return !empty($s);
    }
}
