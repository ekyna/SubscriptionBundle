<?php

namespace Ekyna\Bundle\SubscriptionBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\PaymentBundle\Model\PaymentTransitionTrait;
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
            $stateMachine->apply('exempt');
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
            $stateMachine->apply('unexempt');
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
