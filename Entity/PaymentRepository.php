<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Class PaymentRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentRepository extends EntityRepository
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
        $qb = $this->createQueryBuilder('p');

        $parameters = array('user' => $user);

        $qb
            ->join('p.subscriptions', 's')
            ->andWhere($qb->expr()->eq('s.user', ':user'))
            ->addOrderBy('p.createdAt', 'DESC')
        ;

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult()
        ;
    }
}
