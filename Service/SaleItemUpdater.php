<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Resource\Model\DateRange;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SaleItemUpdater
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemUpdater
{
    public const DESCRIPTION_KEY = 'renewal';

    public function __construct(
        private readonly ContextProviderInterface $contextProvider,
        private readonly FormatterFactory    $formatterFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function updateDescription(SaleItemInterface $item, DateRange $dateRange): bool
    {
        while ($item->isPrivate()) {
            $item = $item->getParent();
        }

        $item->setDescription(self::DESCRIPTION_KEY, $this->buildDescription($item, $dateRange));

        return true;
    }

    public function buildDescription(SaleItemInterface $item, DateRange $dateRange): string
    {
        $formatter = $this->getFormatter($item);

        return $this->translator->trans('field.date_range', [
            '{from}' => $formatter->date($dateRange->getStart()),
            '{to}'   => $formatter->date($dateRange->getEnd()),
        ], 'EkynaUi');
    }

    private function getFormatter(SaleItemInterface $item): Formatter
    {
        if ($sale = $item->getRootSale()) {
            $locale = $sale->getLocale();
        } else {
            $locale = $this->contextProvider->getContext()->getLocale();
        }

        return $this->formatterFactory->create($locale);
    }

    public function updateNetPrice(SaleItemInterface $item, PlanInterface $plan, DateRange $range): bool
    {
        $sale = $item->getRootSale();
        if (null !== $sale && $sale->hasPaidPayments(true)) {
            return false;
        }

        if (null === $product = $plan->getProduct()) {
            throw new RuntimeException('Plan product is not defined');
        }

        // Don't change net price if plan does not use renewal date (anniversary)
        if (null === $plan->getRenewalDate()) {
            return false;
        }

        $duration = $range->getDays();

        $default = $range->getStart()->modify("+{$plan->getInitialDuration()} month")->modify('-1 day');

        $total = $range->getStart()->diff($default)->days;

        $netPrice = $product->getNetPrice()->mul($duration)->div($total)->round(5);

        if ($item->getNetPrice()->equals($netPrice)) {
            return false;
        }

        $item->setNetPrice($netPrice);

        return true;
    }
}
