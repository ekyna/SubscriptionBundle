<?php

namespace Ekyna\Bundle\SubscriptionBundle\Order;

use Ekyna\Bundle\OrderBundle\Helper\OrderHelperInterface;
use Ekyna\Bundle\SubscriptionBundle\Entity\SubscriptionRepository;
use Ekyna\Bundle\SubscriptionBundle\Exception\NoPaymentRequiredSubscriptionException;
use Ekyna\Bundle\SubscriptionBundle\Exception\OrderFeedFailureException;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Sale\Order\OrderInterface;

/**
 * Class OrderFeeder
 * @package Ekyna\Bundle\SubscriptionBundle\Order
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OrderFeeder
{
    /**
     * @var OrderHelperInterface
     */
    protected $orderHelper;

    /**
     * @var SubscriptionRepository
     */
    protected $repository;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param OrderHelperInterface $orderHelper
     * @param SubscriptionRepository $repository
     */
    public function __construct(OrderHelperInterface $orderHelper, SubscriptionRepository $repository, $debug)
    {
        $this->orderHelper = $orderHelper;
        $this->repository  = $repository;
        $this->debug       = $debug;
    }

    /**
     * Feeds the order with all the user "payment required" subscriptions.
     *
     * @param OrderInterface $order
     * @param UserInterface $user
     * @throws \Exception
     * @throws \Ekyna\Bundle\SubscriptionBundle\Exception\SubscriptionException
     */
    public function feed(OrderInterface $order, UserInterface $user)
    {
        $subscriptions = $this->repository->findByUserAndPaymentRequired($user);
        if (empty($subscriptions)) {
            throw new NoPaymentRequiredSubscriptionException();
        }

        foreach ($subscriptions as $subscription) {
            // Don't add twice
            if ($this->orderHelper->hasSubject($order, $subscription)) {
                continue;
            }

            try {
                $event = $this->orderHelper->addSubject($order, $subscription);
                if ($event->isPropagationStopped()) {
                    throw new OrderFeedFailureException();
                }
            } catch(\Exception $e) {
                if ($this->debug) {
                    throw $e;
                }
                throw new OrderFeedFailureException();
            }
        }
    }
}
