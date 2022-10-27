<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\SubscriptionBundle\Form\Type\SaleItemRenewalType;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\PlanRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\RenewalRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalCalculator;
use Ekyna\Bundle\SubscriptionBundle\Service\SaleItemUpdater;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Symfony\Component\Form\FormInterface;

/**
 * Class SaleItemListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemListener
{
    public function __construct(
        private readonly PlanRepositoryInterface    $planRepository,
        private readonly SubjectHelperInterface     $subjectHelper,
        private readonly RenewalRepositoryInterface $renewalRepository,
        private readonly SaleItemUpdater            $saleItemUpdater,
        private readonly RenewalCalculator          $renewalCalculator,
    ) {
    }

    public function onSaleItemBuild(SaleItemEvent $event): void
    {
        $item = $event->getItem();
        if (null !== $this->findRenewal($event)) {
            return;
        }

        $subject = $this->subjectHelper->resolve($item);
        if (!$subject instanceof ProductInterface) {
            return;
        }

        if (null === $plan = $this->planRepository->findOneByProduct($subject)) {
            return;
        }

        $range = null;
        if ($item->hasDatum(RenewalInterface::DATA_KEY)) {
            $range = DateRange::fromString($item->getDatum(RenewalInterface::DATA_KEY));
        }

        if (null === $range || null === $plan) {
            return;
        }

        $this->saleItemUpdater->updateNetPrice($item, $plan, $range);
        $this->saleItemUpdater->updateDescription($item, $range);
    }

    /**
     * Sale item build form event handler.
     */
    public function onSaleItemBuildForm(SaleItemFormEvent $event): void
    {
        $form = $event->getForm();

        // Abort if not admin mode
        if (!$form->getConfig()->getOption('admin_mode')) {
            return;
        }

        $item = $event->getItem();

        if ($item instanceof OrderItemInterface && null !== $this->findRenewal($event)) {
            return;
        }

        $subject = $this->subjectHelper->resolve($item);
        if (!$subject instanceof ProductInterface) {
            return;
        }

        if (null === $plan = $this->planRepository->findOneByProduct($subject)) {
            return;
        }

        // Calculates and sets initial dates
        $start = (new DateTime('+2 weeks'))->modify('Monday'); // TODO Configurable
        $range = $this->renewalCalculator->calculateDateRangeWithPlan($plan, $start);

        $item->setDatum(RenewalInterface::DATA_KEY, $range->toString());

        $event->getForm()->add('renewal', SaleItemRenewalType::class, [
            'getter' => function (SaleItemInterface $item, FormInterface $form): string {
                return $item->getDatum(RenewalInterface::DATA_KEY) ?? '';
            },
            'setter' => function (?SaleItemInterface $item, string $datum, FormInterface $form): void {
                $item->setDatum(RenewalInterface::DATA_KEY, $datum);
            },
        ]);
    }

    private function findRenewal(SaleItemEvent $event): ?RenewalInterface
    {
        if (null !== $renewal = $event->getDatum(RenewalInterface::DATA_KEY)) {
            return $renewal;
        }

        $item = $event->getItem();

        if (!$item instanceof OrderItemInterface) {
            return null;
        }

        if (null === $id = $item->getId()) {
            return null;
        }

        return $this->renewalRepository->findOneByOrderItemId($id);
    }
}
