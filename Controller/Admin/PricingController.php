<?php

namespace Ekyna\Bundle\SubscriptionBundle\Controller\Admin;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CoreBundle\Exception\RedirectException;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvent;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionTransitions;
use Symfony\Component\Console;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Process;

/**
 * Class PricingController
 * @package Ekyna\Bundle\SubscriptionBundle\Controller\Admin
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PricingController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    protected function buildShowData(array &$data, Context $context)
    {
        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface $pricing */
        $pricing = $context->getResource();

        $data['subscriptions'] = $this
            ->get('ekyna_subscription.subscription.repository')
            ->findByPricing($pricing)
        ;

        return null;
    }

    /**
     * Toggle subscription exempt action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \SM\SMException
     */
    public function subscriptionToggleExemptAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface $subscription */
        $subscription = $this
            ->get('ekyna_subscription.subscription.repository')
            ->find($request->attributes->get('subscriptionId'))
        ;
        if (null === $subscription) {
            throw new NotFoundHttpException('Subscription not found');
        }

        $transition = SubscriptionTransitions::TRANSITION_EXEMPT;
        if ($subscription->getState() === SubscriptionStates::STATE_EXEMPT) {
            $transition = SubscriptionTransitions::TRANSITION_UNEXEMPT;
        }

        $stateMachine = $this->get('sm.factory')->get($subscription);
        if ($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $em = $this->getManager();
            $em->persist($subscription);

            $em->flush();

            $this->getDispatcher()->dispatch(
                SubscriptionEvents::STATE_CHANGED,
                new SubscriptionEvent($subscription)
            );
        }

        return $this->redirect(
            $this->generateResourcePath($context->getResource())
        );
    }

    /**
     * Generate and/or notify subscriptions action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateNotifyAction(Request $request)
    {
        // TODO check permission

        $this->checkPricing();

        $this->loadContext($request);

        $cancelPath = $this->generateUrl('ekyna_subscription_pricing_admin_list');

        // TODO Form type
        $form = $this
            ->createFormBuilder(null, array(
                'admin_mode' => true,
                '_redirect_enabled' => true,
            ))
            ->add('years', 'choice', array(
                'label' => 'ekyna_subscription.generate_notify.field.years',
                'choices' => $this->getYearChoices(),
                'multiple' => true,
            ))
            ->add('generate', 'checkbox', array(
                'label' => 'ekyna_subscription.generate_notify.field.generate',
                'required' => false,
                'attr' => array('align_with_widget' => true),
            ))
            ->add('notify', 'checkbox', array(
                'label' => 'ekyna_subscription.generate_notify.field.notify',
                'required' => false,
                'attr' => array('align_with_widget' => true),
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
            $years    = (array) $form->get('years')->getData();
            $generate = (bool)  $form->get('generate')->getData();
            $notify   = (bool)  $form->get('notify')->getData();

            // TODO Flashes
            if ($generate) {
                $notifyArg = $notify ? ' --notify' : '';
                $env = ' --env='.$this->container->getParameter('kernel.environment');
                $process = new Process(
                    'php app/console ekyna:subscription:generate ' . implode(' ', $years) . $notifyArg . $env,
                    dirname($this->container->getParameter('kernel.root_dir'))
                );
                $process->start();

                $this->addFlash('ekyna_subscription.generate_notify.message.generate', 'info');
                if ($notify) {
                    $this->addFlash('ekyna_subscription.generate_notify.message.notify', 'info');
                }
            } elseif ($notify) {
                $env = ' --env='.$this->container->getParameter('kernel.environment');
                $process = new Process(
                    'php app/console ekyna:subscription:notify' . $env,
                    dirname($this->container->getParameter('kernel.root_dir'))
                );
                $process->start();
                $this->addFlash('ekyna_subscription.generate_notify.message.notify', 'info');
            }

            return $this->redirect($cancelPath);
        }

        $this->appendBreadcrumb(
            'pricing-generate-notify',
            'ekyna_subscription.pricing.button.generate_notify'
        );

        return $this->render('EkynaSubscriptionBundle:Admin/Pricing:generate-notify.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Check if at least one pricing is available.
     *
     * @throws RedirectException
     */
    private function checkPricing()
    {
        $qb = $this->getRepository()->createQueryBuilder('p');
        $pricing = $qb
            ->select('p.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult(Query::HYDRATE_SCALAR)
        ;
        if (null === $pricing) {
            throw new RedirectException(
                $this->generateUrl('ekyna_subscription_pricing_admin_list'),
                'No subscription pricing available.',
                'warning'
            );
        }
    }

    /**
     * Returns the years choices.
     *
     * @return array
     */
    private function getYearChoices()
    {
        $years = [];
        /** @var \Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface[] $pricing */
        $pricing = $this->getRepository()->findBy([], ['year' => 'DESC']);
        foreach ($pricing as $p) {
            $year = intval($p->getYear());
            $years[$year] = $year;
        }
        return $years;
    }
}
