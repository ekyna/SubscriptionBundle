<?php

namespace Ekyna\Bundle\SubscriptionBundle\Controller;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Bundle\PaymentBundle\Event\PaymentEvent;
use Ekyna\Bundle\PaymentBundle\Event\PaymentEvents;
use Ekyna\Bundle\SubscriptionBundle\Entity\Payment;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccountController
 * @package Ekyna\Bundle\SubscriptionBundle\Controller
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AccountController extends Controller
{
    /**
     * Renders the account subscription index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $subscriptions = $this
            ->get('ekyna_subscription.subscription.repository')
            ->findByUser($user)
        ;

        $paymentButton = false;
        foreach ($subscriptions as $subscription) {
            if ($subscription->getState() === SubscriptionStates::PENDING) {
                $paymentButton = true;
                break;
            }
        }

        return $this->render('EkynaSubscriptionBundle:Account:index.html.twig', array(
            'subscriptions' => $subscriptions,
            'subscription_payment_button' => $paymentButton,
        ));
    }

    /**
     * Payment action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentAction(Request $request)
    {
        $user = $this->getUser();
        $subscriptions = $this
            ->get('ekyna_subscription.subscription.repository')
            ->findByUser($user, SubscriptionStates::PENDING)
        ;
        if (empty($subscriptions)) {
            $this->addFlash('ekyna_subscription.account.alert.no_pending_subscription', 'info');
            return $this->redirect($this->generateUrl('ekyna_subscription_account_index'));
        }

        $payment = new Payment();
        foreach ($subscriptions as $subscription) {
            $payment->addSubscription($subscription);
        }

        $form = $this->createForm('ekyna_payment_method');

        $form->handleRequest($request);
        if ($form->isValid()) {
            $method = $form->get('method')->getData();

            $payment->setMethod($method);

            $event = new PaymentEvent($payment);
            $this->getDispatcher()->dispatch(PaymentEvents::PREPARE, $event);
            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('EkynaSubscriptionBundle:Account:payment.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
