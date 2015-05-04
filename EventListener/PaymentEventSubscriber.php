<?php

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Ekyna\Bundle\PaymentBundle\Event\PaymentEvent;
use Ekyna\Bundle\PaymentBundle\Event\PaymentEvents;
use Ekyna\Bundle\SubscriptionBundle\Entity\Payment;
use Ekyna\Component\Sale\Payment\PaymentStates;
use SM\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PaymentEventSubscriber
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param ObjectManager         $manager
     * @param FactoryInterface      $factory
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(ObjectManager $manager, FactoryInterface $factory, UrlGeneratorInterface $urlGenerator)
    {
        $this->manager = $manager;
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Payment prepare event handler.
     *
     * @param PaymentEvent $event
     */
    public function onPaymentPrepare(PaymentEvent $event)
    {
        $payment = $event->getPayment();
        if (!$payment instanceof Payment) {
            return;
        }

        $amount = 0;
        foreach ($payment->getSubscriptions() as $subscription) {
            $amount += $subscription->getPrice()->getAmount();
        }
        $payment->setAmount($amount);
    }

    /**
     * Payment state change event handler.
     *
     * @param PaymentEvent $event
     */
    public function onPaymentStateChange(PaymentEvent $event)
    {
        $payment = $event->getPayment();
        if (!$payment instanceof Payment) {
            return;
        }

        if (in_array($payment->getState(), array(PaymentStates::STATE_AUTHORIZED, PaymentStates::STATE_COMPLETED))) {
            foreach ($payment->getSubscriptions() as $subscription) {
                $stateMachine = $this->factory->get($subscription);
                if ($stateMachine->can('pay')) {
                    $stateMachine->apply('pay');
                    $this->manager->persist($subscription);
                }
            }
        } elseif($payment->getState() === PaymentStates::STATE_REFUNDED) {
            foreach ($payment->getSubscriptions() as $subscription) {
                $stateMachine = $this->factory->get($subscription);
                if ($stateMachine->can('refund')) {
                    $stateMachine->apply('refund');
                    $this->manager->persist($subscription);
                }
            }
        }

        $this->manager->flush();
    }

    /**
     * Payment done event handler.
     *
     * @param PaymentEvent $event
     */
    public function onPaymentDone(PaymentEvent $event)
    {
        $payment = $event->getPayment();
        if (!$payment instanceof Payment) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate('ekyna_subscription_account_index')
        ));
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            PaymentEvents::PREPARE      => array('onPaymentPrepare', 0),
            PaymentEvents::STATE_CHANGE => array('onPaymentStateChange', 0),
            PaymentEvents::DONE         => array('onPaymentDone', 0),
        );
    }
}
