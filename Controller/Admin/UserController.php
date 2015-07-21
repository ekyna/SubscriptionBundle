<?php

namespace Ekyna\Bundle\SubscriptionBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\PaymentBundle\Model\PaymentTransitionTrait;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionTransitions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints;

/**
 * Class UserController
 * @package Ekyna\Bundle\SubscriptionBundle\Controller\Admin
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class UserController extends ResourceController
{
    use PaymentTransitionTrait;

    /**
     * Exempt action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exemptAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $context->getResource($resourceName);

        $this->isGranted('EDIT', $user);

        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface $subscription */
        $subscription = $this
            ->get('ekyna_subscription.subscription.repository')
            ->find($request->attributes->get('subscriptionId'))
        ;
        $stateMachine = $this->get('sm.factory')->get($subscription);
        if (null === $subscription || !$stateMachine->can(SubscriptionTransitions::TRANSITION_EXEMPT)) {
            throw new NotFoundHttpException('Subscription not found.');
        }

        $cancelPath = $this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        );

        $options = array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $cancelPath,
                'buttons' => array(
                    'submit' => array(
                        'theme' => 'primary',
                        'icon' => 'ok',
                        'label' => 'ekyna_core.button.validate',
                    )
                )
            ),
        );

        $message = sprintf(
            'Confirmer la dispense de cotisation %s (%s €) ?', // TODO translate
            $subscription->getPrice()->getPricing()->getYear(),
            number_format($subscription->getPrice()->getAmount(), 2, ',', '')
        );

        $form = $this
            ->createFormBuilder(null, $options)
            ->add('confirm', 'checkbox', array(
                'label' => $message,
                'attr' => array('align_with_widget' => true),
                'required' => true,
                'constraints' => array(
                    new Constraints\True(),
                )
            ))
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isValid()) {
            $stateMachine->apply(SubscriptionTransitions::TRANSITION_EXEMPT);
            $em = $this->getManager();
            $em->persist($subscription);
            $em->flush();

            return $this->redirect($cancelPath);
        }

        $this->appendBreadcrumb(
            'user-subscription-exempt',
            'ekyna_subscription.subscription.button.exempt'
        );

        return $this->render(
            'EkynaSubscriptionBundle:Admin/User:subscription_exempt.html.twig',
            $context->getTemplateVars(array(
                'subscription' => $subscription,
                'form' => $form->createView()
            ))
        );
    }

    /**
     * Unexempt action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unexemptAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $context->getResource($resourceName);

        $this->isGranted('EDIT', $user);

        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface $subscription */
        $subscription = $this
            ->get('ekyna_subscription.subscription.repository')
            ->find($request->attributes->get('subscriptionId'))
        ;
        $stateMachine = $this->get('sm.factory')->get($subscription);
        if (null === $subscription || !$stateMachine->can(SubscriptionTransitions::TRANSITION_UNEXEMPT)) {
            throw new NotFoundHttpException('Subscription not found.');
        }

        $cancelPath = $this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        );

        $options = array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $cancelPath,
                'buttons' => array(
                    'submit' => array(
                        'theme' => 'primary',
                        'icon' => 'ok',
                        'label' => 'ekyna_core.button.validate',
                    )
                )
            ),
        );

        $message = sprintf(
            'Confirmer l\'annulation de la dispense de cotisation %s (%s €) ?', // TODO translate
            $subscription->getPrice()->getPricing()->getYear(),
            number_format($subscription->getPrice()->getAmount(), 2, ',', '')
        );

        $form = $this
            ->createFormBuilder(null, $options)
            ->add('confirm', 'checkbox', array(
                'label' => $message,
                'attr' => array('align_with_widget' => true),
                'required' => true,
                'constraints' => array(
                    new Constraints\True(),
                )
            ))
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isValid()) {
            $stateMachine->apply(SubscriptionTransitions::TRANSITION_UNEXEMPT);
            $em = $this->getManager();
            $em->persist($subscription);
            $em->flush();

            return $this->redirect($cancelPath);
        }

        $this->appendBreadcrumb(
            'user-subscription-unexempt',
            'ekyna_subscription.subscription.button.unexempt'
        );

        return $this->render(
            'EkynaSubscriptionBundle:Admin/User:subscription_unexempt.html.twig',
            $context->getTemplateVars(array(
                'subscription' => $subscription,
                'form' => $form->createView()
            ))
        );
    }

    /**
     * Creates an order.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createOrderAction(Request $request)
    {
        throw new NotFoundHttpException('Not yes implemented');

        /*$context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        /*$user = $context->getResource($resourceName);

        $this->isGranted('EDIT', $user);

        $cancelPath = $this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        );

        $data = array('subscriptions' => array());

        $options = array(
            'user' => $user,
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $cancelPath,
                'buttons' => array(
                    'submit' => array(
                        'theme' => 'primary',
                        'icon' => 'ok',
                        'label' => 'ekyna_core.button.validate',
                    )
                )
            ),
        );

        $form = $this->createForm('ekyna_subscription_create_order', $data, $options);

        $form->handleRequest($request);
        if ($form->isValid()) {

            /** @var \Ekyna\Component\Sale\Order\OrderInterface $order */
            /*$order = $this->get('ekyna_order.order.repository')->createNew();
            $order
                ->setUser($user)
                ->setInvoiceAddress($user->getAddresses()->first())
            ;



            $event = $this->get('ekyna_order.order.operator')->create($order);
            $event->toFlashes($this->getFlashBag());
            if (!$event->isPropagationStopped()) {
                return $this->redirect($this->generateUrl('ekyna_order_order_admin_show', array(
                    'orderId' => $order->getId()
                )));
            }
        }

        return $this->render(
            'EkynaSubscriptionBundle:Admin/User:subscription_create_order.html.twig',
            $context->getTemplateVars(array(
                'form' => $form->createView()
            ))
        );*/
    }
}
