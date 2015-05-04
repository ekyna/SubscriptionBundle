<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Sale\Payment\PaymentStates;

/**
 * Class SubscriptionRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionRepository extends EntityRepository
{
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
     * Returns subscriptions that are paid or subject to a processing payment.
     *
     * @param UserInterface $user
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface[]
     */
    public function findByUserAndValidPayment(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->leftJoin('s.payments', 'p')
            ->andWhere($qb->expr()->eq('s.user', ':user'))
            ->andWhere($qb->expr()->in('p.state', ':states'))
        ;

        $parameters = array(
            'user' => $user,
            'states' => array(
                PaymentStates::STATE_AUTHORIZED,
                PaymentStates::STATE_COMPLETED,
                PaymentStates::STATE_PENDING,
                PaymentStates::STATE_PROCESSING,
                PaymentStates::STATE_UNKNOWN,
            ),
        );

        return $qb
            ->distinct()
            ->getQuery()
            ->setParameters($parameters)
            ->getResult()
        ;
    }

    /**
     * Returns subscriptions which require a payment.
     *
     * @param UserInterface $user
     * @return array
     */
    public function findByUserAndPaymentRequired(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('s');

        $processingSubscriptions = $this->findByUserAndValidPayment($user);

        $parameters = array('user' => $user);

        $qb
            ->leftJoin('s.payments', 'payment')
            ->join('s.price', 'price')
            ->join('price.pricing', 'pricing')
            ->andWhere($qb->expr()->eq('s.user', ':user'))
            ->addOrderBy('pricing.year', 'DESC')
        ;

        if (!empty($processingSubscriptions)) {
            $qb->andWhere($qb->expr()->notIn('s', ':processing_subscriptions'));
            $parameters['processing_subscriptions'] = $processingSubscriptions;
        }

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult()
        ;
    }
}
