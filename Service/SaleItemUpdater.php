<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Resource\Model\DateRange;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

use function preg_replace;
use function trim;

/**
 * Class SaleItemUpdater
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemUpdater
{
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

        if ($sale = $item->getRootSale()) {
            $locale = $sale->getLocale();
        } else {
            $locale = $this->contextProvider->getContext()->getLocale();
        }

        $formatter = $this->formatterFactory->create($locale);

        $date = strtr($formatter->date(new DateTime('2000-01-02')), [
            '2000' => '\d{4}',
            '01'   => '\d{2}',
            '02'   => '\d{2}',
        ]);

        $pattern = $this->translator->trans('field.date_range', [
            '{from}' => $date,
            '{to}'   => $date,
        ], 'EkynaUi');

        $description = trim(preg_replace("~$pattern~", '', (string)$item->getDescription()), " \t\n\r\0\x0B.");

        if (!empty($description)) {
            $description .= '. ';
        }

        $description .= $this->translator->trans('field.date_range', [
            '{from}' => $formatter->date($dateRange->getStart()),
            '{to}'   => $formatter->date($dateRange->getEnd()),
        ], 'EkynaUi');

        if ($description === $item->getDescription()) {
            return false;
        }

        $item->setDescription($description);

        return true;
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
