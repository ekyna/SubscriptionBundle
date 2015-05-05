<?php

namespace Ekyna\Bundle\SubscriptionBundle\Subscription\Provider;

use Doctrine\ORM\EntityManager;
use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Class AbstractPriceProvider
 * @package Ekyna\Bundle\SubscriptionBundle\Subscription\Provider
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPriceProvider implements PriceProviderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $priceClass;


    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param $priceClass
     */
    public function __construct(EntityManager $em, $priceClass)
    {
        $this->em = $em;
        $this->priceClass = $priceClass;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function createPriceCollection();

    /**
     * {@inheritdoc}
     */
    abstract public function findPriceByUserAndYear(UserInterface $user, $year);
}
