<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service\Migration;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Service\Migration\DescriptionConverterInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\SaleItemUpdater;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

use function preg_match;
use function strlen;
use function substr;

/**
 * Class DescriptionConverter
 * @package Ekyna\Bundle\SubscriptionBundle\Service\Migration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DescriptionConverter implements DescriptionConverterInterface
{
    public function __construct(
        private readonly FormatterFactory    $formatterFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function convert(array &$result, string &$description): void
    {
        $formatter = $this->formatterFactory->create();

        $date = strtr($formatter->date(new DateTime('2000-01-02')), [
            '2000' => '\d{4}',
            '01'   => '\d{2}',
            '02'   => '\d{2}',
        ]);

        $pattern = $this->translator->trans('field.date_range', [
            '{from}' => $date,
            '{to}'   => $date,
        ], 'EkynaUi');

        preg_match("~$pattern~", $description, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches)) {
            return;
        }

        /** @var array<array{0: string, 1:int}> $matches */
        if ($matches[0][0] === $description) {
            $result[SaleItemUpdater::DESCRIPTION_KEY] = $description;
            $description = '';

            return;
        }

        $extract = trim(substr($description, $matches[0][1], strlen($matches[0][0])));
        $result[SaleItemUpdater::DESCRIPTION_KEY] = $extract;

        $extract = substr($description, 0, $matches[0][1])
            . substr($description, $matches[0][1] + strlen($matches[0][0]));

        $description = trim($extract, " \t\n\r\0\x0B.");
    }
}
