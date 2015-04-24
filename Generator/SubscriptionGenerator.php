<?php

namespace Ekyna\Bundle\SubscriptionBundle\Generator;

use Doctrine\ORM\EntityManager;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvent;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\Exception\GenerationException;
use Ekyna\Bundle\SubscriptionBundle\Generator\Provider\PriceProviderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PriceProviderSubjectInterface;
use Ekyna\Bundle\SubscriptionBundle\Util\Year;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SubscriptionGenerator
 * @package Ekyna\Bundle\SubscriptionBundle\Generator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionGenerator implements PriceProviderSubjectInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @var string
     */
    private $subscriptionClass;

    /**
     * @var PriceProviderInterface
     */
    private $pricingProvider;


    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param EventDispatcherInterface $dispatcher
     * @param ValidatorInterface       $validator
     * @param string                   $userClass
     * @param string                   $subscriptionClass
     */
    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $dispatcher,
        ValidatorInterface $validator,
        $userClass,
        $subscriptionClass
    ) {
        $this->em         = $em;
        $this->validator  = $validator;
        $this->dispatcher = $dispatcher;

        $this->userClass         = $userClass;
        $this->subscriptionClass = $subscriptionClass;
    }

    /**
     * Sets the price provider.
     *
     * @param PriceProviderInterface $provider
     */
    public function setPriceProvider(PriceProviderInterface $provider)
    {
        $this->pricingProvider = $provider;
    }

    /**
     * Generates the subscriptions by year.
     *
     * @param string $year
     * @return int
     */
    public function generateByYear($year = null)
    {
        $year = Year::validate($year);

        $users = $this->findNonSubscribedUsers($year);

        $count = 0;
        foreach ($users as $user) {
            if (null !== $this->generateByUserAndYear($user, $year)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generates the subscription by user and year
     *
     * @param UserInterface $user
     * @param string        $year
     * @return \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface|null
     */
    public function generateByUserAndYear(UserInterface $user, $year)
    {
        $year = Year::validate($year);

        if (null === $price = $this->pricingProvider->findPriceByUserAndYear($user, $year)) {
            throw new GenerationException('No price found (%s).', $year);
        }
        if (false === $price) {
            return null;
        }

        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface $subscription */
        $subscription = new $this->subscriptionClass;
        $subscription
            ->setUser($user)
            ->setPrice($price)
        ;

        $event = new SubscriptionEvent($subscription);

        $this->dispatcher->dispatch(SubscriptionEvents::PRE_GENERATE, $event);
        if ($event->isPropagationStopped()) {
            return null;
        }

        /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errorList */
        $errorList = $this->validator->validate($subscription);
        if (0 < $errorList->count()) {
            throw new GenerationException('Invalid subscription');
        }

        $this->em->persist($subscription);
        $this->em->flush();

        $this->dispatcher->dispatch(SubscriptionEvents::POST_GENERATE, $event);

        return $subscription;
    }

    /**
     * Selects non subscribed users.
     *
     * @param string $year
     * @return UserInterface[]
     */
    private function findNonSubscribedUsers($year)
    {
        $selectNonSubscribedUsersDql = <<<DQL
SELECT u FROM %s u
WHERE u.id NOT IN (
    SELECT u.id FROM %s s
    JOIN s.user su
    JOIN s.price sp
    JOIN sp.pricing sy
    WHERE sy.year = :year
)
DQL;

        $dql = sprintf($selectNonSubscribedUsersDql, $this->userClass, $this->subscriptionClass);

        return $this->em
            ->createQuery($dql)
            ->setParameter('year', $year)
            ->getResult()
        ;
    }
}
