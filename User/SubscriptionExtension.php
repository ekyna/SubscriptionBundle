<?php

namespace Ekyna\Bundle\SubscriptionBundle\User;

use Ekyna\Bundle\SubscriptionBundle\Entity\PaymentRepository;
use Ekyna\Bundle\SubscriptionBundle\Entity\SubscriptionRepository;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\UserBundle\Extension\AbstractExtension;
use Ekyna\Bundle\UserBundle\Extension\Admin\ShowTab;
use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Class SubscriptionExtension
 * @package Ekyna\Bundle\SubscriptionBundle\User
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionExtension extends AbstractExtension
{
    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;


    /**
     * Constructor.
     *
     * @param SubscriptionRepository $subscriptionRepository
     * @param PaymentRepository      $paymentRepository
     */
    public function __construct(SubscriptionRepository $subscriptionRepository, PaymentRepository $paymentRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->paymentRepository      = $paymentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminShowTab(UserInterface $user)
    {
        $data = [
            'subscriptions'          => $this->subscriptionRepository->findByUser($user),
            'payments'               => $this->paymentRepository->findByUser($user),
            'display_payment_button' => $this->subscriptionRepository->userHasPaymentRequiredSubscriptions($user),
        ];

        return new ShowTab(
            'ekyna_subscription.label',
            $data,
            'EkynaSubscriptionBundle:Admin/User:subscription_tab.html.twig'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountMenuEntries()
    {
        return array('subscription' => array(
            'label' => 'ekyna_subscription.account.menu',
            'route' => 'ekyna_subscription_account_index',
            'position' => 10,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'subscription';
    }
}
