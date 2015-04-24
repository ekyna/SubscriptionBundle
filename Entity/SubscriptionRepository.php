<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\UserBundle\Model\UserInterface;

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
     * @param string        $state
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface[]
     */
    public function findByUser(UserInterface $user, $state = null)
    {
        $qb = $this->createQueryBuilder('s');

        $parameters = array('user' => $user);

        $qb
            ->join('s.price', 'p')
            ->join('p.pricing', 'y')
            ->andWhere($qb->expr()->eq('s.user', ':user'))
            ->addOrderBy('y.year', 'DESC')
        ;

        if (null !== $state) {
            SubscriptionStates::isValid($state, true);
            $qb->andWhere($qb->expr()->eq('s.state', ':state'));
            $parameters['state'] = $state;
        }

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult()
        ;
    }
}
