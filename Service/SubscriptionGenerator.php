<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Bundle\SubscriptionBundle\Entity\Renewal;
use Ekyna\Bundle\SubscriptionBundle\Factory\RenewalFactoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\PlanRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Generator;

use function in_array;

/**
 * Class SubscriptionGenerator
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionGenerator
{
    public const RENEW_PAYMENT_STATES = [
        PaymentStates::STATE_CAPTURED,
        PaymentStates::STATE_DEPOSIT,
        PaymentStates::STATE_COMPLETED,
    ];

    public const RENEW_SHIPMENT_STATES = [
        ShipmentStates::STATE_PARTIAL,
        ShipmentStates::STATE_COMPLETED,
    ];

    private PlanRepositoryInterface         $planRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private ResourceFactoryInterface        $subscriptionFactory;
    private RenewalFactoryInterface         $renewalFactory;
    private RenewalUpdater                  $renewalUpdater;

    public function __construct(
        PlanRepositoryInterface         $planRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        ResourceFactoryInterface        $subscriptionFactory,
        RenewalFactoryInterface         $renewalFactory,
        RenewalUpdater                  $renewalUpdater
    ) {
        $this->planRepository = $planRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->renewalFactory = $renewalFactory;
        $this->renewalUpdater = $renewalUpdater;
    }

    private function isPaidOrDelivered(OrderInterface $order): bool
    {
        if (in_array($order->getPaymentState(), self::RENEW_PAYMENT_STATES, true)) {
            return true;
        }

        return in_array($order->getShipmentState(), self::RENEW_SHIPMENT_STATES, true);
    }

    /**
     * Generate subscriptions from order.
     *
     * @param OrderInterface $order
     *
     * @return Generator<SubscriptionInterface>
     */
    public function generateFromOrder(OrderInterface $order): Generator
    {
        if ($order->isSample()) {
            return;
        }

        $renew = $this->isPaidOrDelivered($order);

        $customer = $order->getCustomer();

        $plans = $this->planRepository->findAll();

        foreach ($plans as $plan) {
            $subscription = null;

            foreach ($this->findItemsForPlan($order->getItems(), $plan) as $item) {
                if (null === $subscription) {
                    $subscription = $this->findOrCreateSubscription($plan, $customer);
                }

                if ($renew) {
                    if (null !== $this->renew($subscription, $item)) {
                        yield $subscription;
                    }

                    continue;
                }

                if (null !== $this->cancel($subscription, $item)) {
                    yield $subscription;
                }
            }
        }
    }

    private function findOrCreateSubscription(PlanInterface $plan, CustomerInterface $customer): SubscriptionInterface
    {
        $subscription = $this->subscriptionRepository->findOneByPlanAndCustomer($plan, $customer);
        if ($subscription) {
            return $subscription;
        }

        /** @var SubscriptionInterface $subscription */
        $subscription = $this
            ->subscriptionFactory
            ->create();

        return $subscription
            ->setPlan($plan)
            ->setCustomer($customer);
    }

    protected function renew(SubscriptionInterface $subscription, OrderItemInterface $item): ?Renewal
    {
        $renewal = $this->findRenewalByOrderItem($subscription, $item);

        if (null !== $renewal) {
            if (!$renewal->isPaid()) {
                $renewal->setPaid(true);

                return $renewal;
            }

            return null;
        }

        /** @var RenewalInterface $renewal */
        $renewal = $this->renewalFactory->create();

        $renewal
            ->setSubscription($subscription)
            ->setOrderItem($item)
            ->setPaid(true);

        // Use user defined date range
        if ($item->hasDatum(RenewalInterface::DATA_KEY)) {
            $datum = $item->getDatum(RenewalInterface::DATA_KEY);
            if (null !== $range = DateRange::fromString($datum)) {
                $renewal->setDateRange($range);
            }
        }

        $this->renewalUpdater->update($renewal);

        return $renewal;
    }

    protected function cancel(SubscriptionInterface $subscription, OrderItemInterface $order): ?Renewal
    {
        $renewal = $this->findRenewalByOrderItem($subscription, $order);

        if (null === $renewal) {
            return null;
        }

        if (!$renewal->isPaid()) {
            return null;
        }

        $renewal->setPaid(false);

        return $renewal;
    }

    private function findRenewalByOrderItem(SubscriptionInterface $subscription, OrderItemInterface $item): ?Renewal
    {
        foreach ($subscription->getRenewals() as $renewal) {
            if ($renewal->getOrderItem() === $item) {
                return $renewal;
            }
        }

        return null;
    }

    /**
     * Returns the plan's identifiers associated with the given items.
     *
     * @param Collection<OrderItemInterface> $items
     * @param PlanInterface                  $plan
     *
     * @return Generator<OrderItemInterface>
     */
    private function findItemsForPlan(Collection $items, PlanInterface $plan): Generator
    {
        foreach ($items as $item) {
            if ($item->hasChildren()) {
                yield from $this->findItemsForPlan($item->getChildren(), $plan);
            }

            if (!$item->hasSubjectIdentity()) {
                continue;
            }

            if ($item->getSubjectIdentity()->getProvider() !== ProductProvider::getName()) {
                continue;
            }

            if ($item->getSubjectIdentity()->getIdentifier() === $plan->getProduct()->getIdentifier()) {
                yield $item;
            }
        }
    }
}
