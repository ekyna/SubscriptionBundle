<?php

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Ekyna\Bundle\OrderBundle\Event\OrderEvent;
use Ekyna\Bundle\OrderBundle\Event\OrderEvents;
use Ekyna\Bundle\OrderBundle\Helper\ItemHelperInterface;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvent;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionTransitions;
use Ekyna\Component\Sale\Order\OrderInterface;
use Ekyna\Component\Sale\Order\OrderStates;
use SM\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ItemHelperInterface
     */
    protected $itemHelper;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;


    /**
     * Constructor.
     *
     * @param ItemHelperInterface $itemHelper
     * @param ObjectManager       $manager
     * @param FactoryInterface    $factory
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ItemHelperInterface      $itemHelper,
        ObjectManager            $manager,
        FactoryInterface         $factory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->itemHelper = $itemHelper;
        $this->manager    = $manager;
        $this->factory    = $factory;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Order post state change event handler.
     *
     * @param OrderEvent $event
     */
    public function onPostStateChange(OrderEvent $event)
    {
        $order = $event->getOrder();

        $subscriptions = $this->getSubscriptions($order);
        if (empty($subscriptions)) {
            return;
        }

        if (in_array($order->getState(), array(OrderStates::STATE_ACCEPTED, OrderStates::STATE_COMPLETED))) {
            $this->applyTransition($subscriptions, SubscriptionTransitions::TRANSITION_VALIDATE);
        } else if ($order->getState() === OrderStates::STATE_PENDING) {
            $this->applyTransition($subscriptions, SubscriptionTransitions::TRANSITION_LOCK);
        } else {
            $this->applyTransition($subscriptions, SubscriptionTransitions::TRANSITION_UNLOCK);
        }
    }

    /**
     * Extract the subscription from the given order.
     *
     * @param OrderInterface $order
     * @return array|SubscriptionInterface[]
     */
    protected function getSubscriptions(OrderInterface $order)
    {
        $subscriptions = array();
        foreach ($order->getItems() as $item) {
            if ($item->getSubjectType() === 'subscription') {
                $subscriptions[] = $this->itemHelper->reverseTransform($item);
            }
        }
        return $subscriptions;
    }

    /**
     * Applies the state to the subscriptions.
     *
     * @param array|SubscriptionInterface[] $subscriptions
     * @param string $transition
     */
    protected function applyTransition(array $subscriptions, $transition)
    {
        $subscriptionsStateChanged = [];

        foreach ($subscriptions as $subscription) {
            $stateMachine = $this->factory->get($subscription);
            if ($stateMachine->can($transition)) {
                $stateMachine->apply($transition);
                $this->manager->persist($subscription);
                $subscriptionsStateChanged[] = $subscription;
            }
        }

        $this->manager->flush();

        foreach ($subscriptionsStateChanged as $subscription) {
            $this->dispatcher->dispatch(
                SubscriptionEvents::STATE_CHANGED,
                new SubscriptionEvent($subscription)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            OrderEvents::STATE_CHANGE => array('onPostStateChange', -1024)
        );
    }
}
