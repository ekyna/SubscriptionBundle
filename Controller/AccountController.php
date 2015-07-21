<?php

namespace Ekyna\Bundle\SubscriptionBundle\Controller;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Bundle\SubscriptionBundle\Exception\SubscriptionException;
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

        $subscriptionRepository = $this->get('ekyna_subscription.subscription.repository');

        $subscriptions = $subscriptionRepository->findByUser($user);

        $paymentButton = $subscriptionRepository->userHasPaymentRequiredSubscriptions($user);

        return $this->render('EkynaSubscriptionBundle:Account:index.html.twig', array(
            'subscriptions'          => $subscriptions,
            'display_payment_button' => $paymentButton,
        ));
    }

    /**
     * Payment action.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function paymentAction()
    {
        $redirectPath = $this->generateUrl('ekyna_subscription_account_index');

        $cart = $this->get('ekyna_cart.cart.provider')->getCart();
        $user = $this->getUser();

        try {
            $this->get('ekyna_subscription.order.order_feeder')->feed($cart, $user);
        } catch(SubscriptionException $e) {
            $this->addFlash('Error lors de la création de votre bon de commande.');
            return $this->redirect($redirectPath);
        }

        return $this->redirect($this->generateUrl('ekyna_cart_index'));
    }
}
