<?php

namespace Ekyna\Bundle\SubscriptionBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\OrderBundle\Exception\OrderException;
use Ekyna\Bundle\PaymentBundle\Model\PaymentTransitionTrait;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvent;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
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

        $message = $this->getTranslator()->trans('ekyna_subscription.subscription.confirm.exempt.full', array(
            '{{year}}' => $subscription->getPrice()->getPricing()->getYear(),
            '{{amount}}' => number_format($subscription->getPrice()->getAmount(), 2, ',', ''), // TODO localized format
        ));

        $form = $this
            ->createFormBuilder(null, array(
                'admin_mode' => true,
                '_redirect_enabled' => true,
            ))
            ->add('confirm', 'checkbox', array(
                'label' => $message,
                'attr' => array('align_with_widget' => true),
                'required' => true,
                'constraints' => array(
                    new Constraints\True(),
                )
            ))
            ->add('actions', 'form_actions', [
                'buttons' => [
                    'validate' => [
                        'type' => 'submit', 'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.validate',
                            'attr' => [
                                'icon' => 'ok',
                            ],
                        ],
                    ],
                    'cancel' => [
                        'type' => 'button', 'options' => [
                            'label' => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link' => true,
                            'attr' => [
                                'class' => 'form-cancel-btn',
                                'icon' => 'remove',
                                'href' => $cancelPath,
                            ],
                        ],
                    ],
                ],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isValid()) {
            $stateMachine->apply(SubscriptionTransitions::TRANSITION_EXEMPT);
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
        if (null === $subscription || !$stateMachine->can(SubscriptionTransitions::TRANSITION_UNEXEMPT)) {
            throw new NotFoundHttpException('Subscription not found.');
        }

        $cancelPath = $this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        );

        $message = $this->getTranslator()->trans('ekyna_subscription.subscription.confirm.unexempt.full', array(
            '{{year}}' => $subscription->getPrice()->getPricing()->getYear(),
            '{{amount}}' => number_format($subscription->getPrice()->getAmount(), 2, ',', ''), // TODO localized format
        ));

        $form = $this
            ->createFormBuilder(null, array(
                'admin_mode' => true,
                '_redirect_enabled' => true,
            ))
            ->add('confirm', 'checkbox', array(
                'label' => $message,
                'attr' => array('align_with_widget' => true),
                'required' => true,
                'constraints' => array(
                    new Constraints\True(),
                )
            ))
            ->add('actions', 'form_actions', [
                'buttons' => [
                    'validate' => [
                        'type' => 'submit', 'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.validate',
                            'attr' => [
                                'icon' => 'ok',
                            ],
                        ],
                    ],
                    'cancel' => [
                        'type' => 'button', 'options' => [
                            'label' => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link' => true,
                            'attr' => [
                                'class' => 'form-cancel-btn',
                                'icon' => 'remove',
                                'href' => $cancelPath,
                            ],
                        ],
                    ],
                ],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isValid()) {
            $stateMachine->apply(SubscriptionTransitions::TRANSITION_UNEXEMPT);
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
     * Creates an order.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createOrderAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $context->getResource();
        $cancelPath = $this->generateResourcePath($user);

        // Check for addresses.
        if (0 == $user->getAddresses()->count()) {
            $this->addFlash('ekyna_subscription.subscription.message.create_address_before_order', 'warning');
            return $this->redirect($cancelPath);
        }

        $this->isGranted('EDIT', $user);

        $form = $this->createForm('ekyna_subscription_create_order', null, array(
            'user' => $user,
            'admin_mode' => true,
            '_redirect_enabled' => true,
        ));
        $form->add('actions', 'form_actions', [
            'buttons' => [
                'remove' => [
                    'type' => 'submit', 'options' => [
                        'button_class' => 'primary',
                        'label' => 'ekyna_core.button.validate',
                        'attr' => [
                            'icon' => 'ok',
                        ],
                    ],
                ],
                'cancel' => [
                    'type' => 'button', 'options' => [
                        'label' => 'ekyna_core.button.cancel',
                        'button_class' => 'default',
                        'as_link' => true,
                        'attr' => [
                            'icon' => 'remove',
                            'href' => $cancelPath,
                        ],
                    ],
                ],
            ],
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {

            /** @var \Ekyna\Component\Sale\Order\OrderInterface $order */
            $order = $this->get('ekyna_order.order.repository')->createNew();
            $order
                ->setUser($user)
                ->setInvoiceAddress($user->getAddresses()->first())
            ;

            // TODO validate order ?

            $operator = $this->get('ekyna_order.order.operator');
            $event = $operator->create($order);
            if (!$event->isPropagationStopped()) {

                // Add the selected subscriptions.
                $orderHelper = $this->get('ekyna_order.order_helper');
                $subscriptions = $form->get('subscriptions')->getData();
                foreach ($subscriptions as $subscription) {
                    try {
                        /** @var \Ekyna\Bundle\OrderBundle\Event\OrderItemEvent $subscriptionEvent */
                        $subscriptionEvent = $orderHelper->addSubject($order, $subscription);
                        if (!$subscriptionEvent->isPropagationStopped()) {
                            $event->addMessages($subscriptionEvent->getMessages());
                        }
                    } catch(OrderException $e) {
                        // Removes the order as it failed.
                        $this->get('ekyna_order.order.operator')->delete($order);
                        $this->addFlash('ekyna_subscription.subscription.message.order_failure', 'danger');
                        return $this->redirect($cancelPath);
                    }
                }

                $event->toFlashes($this->getFlashBag());

                return $this->redirect($this->generateUrl('ekyna_order_order_admin_show', array(
                    'orderId' => $order->getId()
                )));
            }
            $event->toFlashes($this->getFlashBag());
        }

        $this->appendBreadcrumb(
            'user-subscription-create-order',
            'ekyna_subscription.subscription.button.create_order'
        );

        return $this->render(
            'EkynaSubscriptionBundle:Admin/User:subscription_create_order.html.twig',
            $context->getTemplateVars(array(
                'form' => $form->createView()
            ))
        );
    }
}
