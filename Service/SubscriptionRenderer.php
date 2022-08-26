<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use OzdemirBurak\Iris\Color\Hsl;

use function array_filter;
use function array_replace_recursive;
use function count;
use function floor;
use function sprintf;
use function usort;

use const PHP_EOL;

/**
 * Class SubscriptionRenderer
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionRenderer
{
    public function __construct(private readonly ResourceHelper $resourceHelper)
    {
    }

    public function renderSvgGraph(array $subscriptions, array $options = []): string
    {
        $renewals = [];

        foreach ($subscriptions as $subscription) {
            if (!$subscription instanceof SubscriptionInterface) {
                throw new UnexpectedTypeException($subscription, SubscriptionInterface::class);
            }

            $renewals = array_merge($renewals, SubscriptionUtils::filterRenewals($subscription));
        }

        if (empty($renewals)) {
            return '';
        }

        $renewals = SubscriptionUtils::sortRenewals($renewals, false);

        $options = array_replace_recursive([
            'width'     => 1200,
            'height'    => 500,
            'class'     => 'img-responsive',
            'rectangle' => [
                'padding'    => 2,
                'min_height' => 28,
                'multiplier' => 4,
            ],
            'text'      => [
                'offset' => 4,
                'size'   => '14px',
            ],
        ], $options);

        $dayInSec = 60 * 60 * 24;
        $colorStep = floor(255 / count($renewals));

        $first = reset($renewals);
        $offset = floor($first->getStartsAt()->getTimestamp() / $dayInSec);
        $calcX = static fn(DateTimeInterface $date): int => (int)($date->getTimestamp() / $dayInSec - $offset);

        $maxWidth = $maxHeight = null;
        $color = 0;
        $rectangles = [];

        $calcY = static function (array $rectangles, int $x, int $height): int {
            // Removes rectangles that do not hit X
            $filter = static fn(array $rect): bool => $rect['x'] + $rect['width'] > $x;
            $rectangles = array_filter($rectangles, $filter);

            // Sort rectangles by Y asc
            usort($rectangles, static fn(array $a, array $b): int => $a['y'] <=> $b['y']);

            $y = 0;
            foreach ($rectangles as $rect) {
                // If there is enough room above this rect
                if ($rect['y'] >= $y + $height) {
                    $y = 0;

                    continue;
                }

                // (else) Grow y
                $t = $rect['y'] + $rect['height'];
                if ($t > $y) {
                    $y = $t;
                }
            }

            return $y;
        };

        $minHeight = $options['rectangle']['min_height'];
        $yPerCount = $options['rectangle']['multiplier'];

        $startYear = $endYear = null;

        foreach ($renewals as $renewal) {
            $x = $calcX($start = $renewal->getStartsAt());
            $width = $calcX($end = $renewal->getEndsAt()) - $x;

            $year = $start->format('Y');
            if (null === $startYear || $startYear > $year) {
                $startYear = $year;
            }
            $year = $end->format('Y');
            if (null === $endYear || $endYear < $year) {
                $endYear = $year;
            }

            $count = $renewal->getCount();
            $height = $count * $yPerCount;
            if ($minHeight > $height) {
                $height = $minHeight;
            }

            $y = $calcY($rectangles, $x, $height);

            $max = $x + $width;
            if (null === $maxWidth || $maxWidth < $max) {
                $maxWidth = $max;
            }

            $max = $y + $height;
            if (null === $maxHeight || $maxHeight < $max) {
                $maxHeight = $max;
            }

            $order = $renewal->getOrder();

            $rectangles[] = [
                'x'       => $x,
                'y'       => $y,
                'width'   => $width,
                'height'  => $height,
                'color'   => (string)new Hsl($color . ',80,80'),
                'order'   => $order->getNumber(),
                'path'    => $this->resourceHelper->generateResourcePath($order, ReadAction::class),
                'summary' => $this->resourceHelper->generateResourcePath($order, SummaryAction::class),
                'count'   => $count,
            ];

            $color += $colorStep;
        }

        $dateHeight = 20;

        $padding = $options['rectangle']['padding'];
        $textOffset = $options['text']['offset'];
        $textSize = $options['text']['size'];

        $xScale = 1;
        if ($maxWidth > $options['width']) {
            $xScale = $options['width'] / $maxWidth;
        } else {
            $options['width'] = $maxWidth;
        }

        $yScale = 1;
        if ($maxHeight > $options['height']) {
            $yScale = $options['height'] / $maxHeight;
        } else {
            $options['height'] = $maxHeight;
        }

        $options['height'] += $dateHeight;

        $code = sprintf(
                '<svg class="%s" width="%d" height="%d" viewBox="0 0 %d %d" style="margin-bottom:20px">',
                $options['class'],
                $options['width'],
                $options['height'],
                $options['width'],
                $options['height'],
            ) . PHP_EOL;

        for ($year = $startYear; $year <= $endYear; $year++) {
            $x = $calcX(new DateTime($year . '-01-01')) * $xScale;

            if (0 > $x || $x > $options['width']) {
                continue;
            }

            $code .= sprintf(
                    '<line x1="%d" y1="0" x2="%d" y2="%d" style="stroke:#000;stroke-width:1" />',
                    $x, $x, $dateHeight - $padding
                ) . PHP_EOL;

            $code .= sprintf(
                    '<text x="%d" y="%d" style="fill:#000;font-size:%s">%s</text>',
                    $x + $textOffset,
                    $dateHeight - $textOffset,
                    $textSize,
                    $year
                ) . PHP_EOL;
        }

        foreach ($rectangles as $rect) {
            $rect['x'] *= $xScale;
            $rect['width'] *= $xScale;
            $rect['y'] *= $yScale;
            $rect['height'] *= $yScale;
            $rect['y'] += $dateHeight;

            /** @noinspection HtmlUnknownTarget */
            $code .= sprintf(
                    '<a href="%s" data-side-detail="%s">',
                    $rect['path'],
                    $rect['summary']
                ) . PHP_EOL;

            $code .= sprintf(
                    '<rect class="renewal" x="%d" y="%d" width="%d" height="%d" style="fill:%s" />',
                    $rect['x'] + $padding,
                    $rect['y'] + $padding,
                    $rect['width'] - 2 * $padding,
                    $rect['height'] - 2 * $padding,
                    $rect['color']
                ) . PHP_EOL;

            $code .= sprintf(
                    '<text x="%d" y="%d" style="fill:#000;font-size:%s">%s (%s)</text>',
                    $rect['x'] + $padding + $textOffset,
                    $rect['y'] + $rect['height'] - $padding - $textOffset,
                    $textSize,
                    $rect['order'],
                    $rect['count']
                ) . PHP_EOL;

            $code .= '</a>' . PHP_EOL;
        }

        $code .= <<<HTML
</svg>
HTML;

        return $code;
    }
}
