<?php

namespace Ekyna\Bundle\SubscriptionBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\PaymentBundle\Event\PaymentEvent;
use Ekyna\Bundle\PaymentBundle\Event\PaymentEvents;
use Ekyna\Bundle\PaymentBundle\Model\PaymentTransitionTrait;
use Ekyna\Bundle\SubscriptionBundle\Entity\Payment;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvent;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
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
        if (null === $subscription || !$stateMachine->can('exempt')) {
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

        $message = $this->getTranslator()->trans('ekyna_subscription.subscription.confirm.exempt', array(
            '%year%' => $subscription->getPrice()->getPricing()->getYear(),
            '%amount%' => number_format($subscription->getPrice()->getAmount(), 2, ',', ''),
        ));

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
            $stateMachine->apply('exempt');
            $em = $this->getManager();
            $em->persist($subscription);
            $em->flush();

            $this->getDispatcher()->dispatch(
                SubscriptionEvents::STATE_CHANGED,
                new SubscriptionEvent($subscription)
            );

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
        if (null === $subscription || !$stateMachine->can('unexempt')) {
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

        $message = $this->getTranslator()->trans('ekyna_subscription.subscription.confirm.unexempt', array(
            '%year%' => $subscription->getPrice()->getPricing()->getYear(),
            '%amount%' => number_format($subscription->getPrice()->getAmount(), 2, ',', ''),
        ));

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
            $stateMachine->apply('unexempt');
            $em = $this->getManager();
            $em->persist($subscription);
            $em->flush();

            $this->getDispatcher()->dispatch(
                SubscriptionEvents::STATE_CHANGED,
                new SubscriptionEvent($subscription)
            );

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
     * Payment action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $context->getResource($resourceName);

        $this->isGranted('EDIT', $user);

        $cancelPath = $this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        );

        $payment = new Payment();
        $payment->setDetails(array(
            'done_redirect_path' => $this->generateUrl(
                $this->config->getRoute('show'),
                $context->getIdentifiers(true)
            ),
        ));

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

        $form = $this->createForm('ekyna_subscription_payment', $payment, $options);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $event = new PaymentEvent($payment);
            $this->getDispatcher()->dispatch(PaymentEvents::PREPARE, $event);
            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render(
            'EkynaSubscriptionBundle:Admin/User:subscription_payment.html.twig',
            $context->getTemplateVars(array(
                'form' => $form->createView()
            ))
        );
    }

    /**
     * Payment transition action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function paymentTransitionAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $context->getResource($resourceName);

        $this->isGranted('EDIT', $user);

        /** @var \Ekyna\Bundle\SubscriptionBundle\Entity\Payment $payment */
        $payment = $this->get('ekyna_subscription.payment.repository')->find(
            $request->attributes->get('paymentId')
        );
        if (null === $payment) { // TODO check that the payment belongs to the user
            throw new NotFoundHttpException('Payment not found');
        }

        $this->applyPaymentTransition($payment, $request->attributes->get('transition'));

        return $this->redirect($this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        ));
    }
}
