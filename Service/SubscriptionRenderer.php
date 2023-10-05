<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Model\Ui;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use OzdemirBurak\Iris\Color\Hsl;

use function array_filter;
use function array_replace_recursive;
use function array_reverse;
use function ceil;
use function count;
use function floor;
use function implode;
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

            $renewals = array_merge($renewals, $subscription->getRenewals()->toArray());
        }

        if (empty($renewals)) {
            return '';
        }

        $renewals = SubscriptionUtils::sortRenewals($renewals, false);

        $options = array_replace_recursive([
            'width'     => 1200,
            'height'    => 650,
            'class'     => 'img-responsive',
            'rectangle' => [
                'padding'    => 2,
                'min_height' => 50,
                'multiplier' => 4,
            ],
            'text'      => [
                'offset' => 4,
                'size'   => 12,
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
            $plan = $renewal->getSubscription()->getPlan();

            $notifications = [];
            foreach ($plan->getReminders() as $reminder) {
                $notification = NotificationHelper::findRenewalNotificationByReminder($renewal, $reminder);
                $notifications[$reminder->getDays()] = null !== $notification;
            }

            $rectangles[] = [
                'x'             => $x,
                'y'             => $y,
                'width'         => $width,
                'height'        => $height,
                'color'         => (string)new Hsl($color . ',80,80'),
                'order'         => $order->getNumber(),
                'plan'          => $plan->getDesignation(),
                'path'          => $this->resourceHelper->generateResourcePath($renewal, ReadAction::class),
                'summary'       => $this->resourceHelper->generateResourcePath($order, SummaryAction::class),
                'count'         => $count,
                'paid'          => $renewal->isPaid(),
                'notifications' => $notifications,
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

        $code .= <<<EOL
<style>
    a.renewal:hover { 
        text-decoration: none;
    }
    a.renewal:hover > rect {
        stroke: black;
        stroke-width: 1px;
    }
    a.renewal:hover > text.order {
        font-weight: bold;
    }
</style>
EOL;


        for ($year = $startYear; $year <= $endYear; $year++) {
            $x = $calcX(new DateTime($year . '-01-01')) * $xScale;

            if (0 > $x || $x > $options['width']) {
                continue;
            }

            $code .= sprintf(
                    '<line x1="%d" y1="0" x2="%d" y2="%d" style="stroke:#000;stroke-width:1" />',
                    $x,
                    $x,
                    $dateHeight - $padding
                ) . PHP_EOL;

            $code .= sprintf(
                    '<text x="%d" y="%d" style="fill:#000;font-size:%spx">%s</text>',
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
            /** @noinspection HtmlUnknownAttribute */
            $code .= sprintf(
                    '<a href="%s" class="renewal" %s="%s">',
                    $rect['path'],
                    Ui::SIDE_DETAIL_ATTR,
                    $rect['summary']
                ) . PHP_EOL;

            $style = $rect['paid']
                ? 'fill:' . $rect['color']
                : 'fill:transparent;stroke-width:3;stroke:' . $rect['color'];

            $code .= sprintf(
                    '<rect x="%d" y="%d" width="%d" height="%d" style="%s" />',
                    $rect['x'] + $padding,
                    $rect['y'] + $padding,
                    $rect['width'] - 2 * $padding,
                    $rect['height'] - 2 * $padding,
                    $style
                ) . PHP_EOL;

            $code .= sprintf(
                    '<text class="order" x="%d" y="%d" style="fill:#000;font-size:%spx">%s (%s)</text>',
                    $rect['x'] + $padding + $textOffset,
                    $rect['y'] + ceil($textSize * 1.2) + $padding,
                    $textSize,
                    $rect['order'],
                    $rect['count']
                ) . PHP_EOL;

            $code .= sprintf(
                    '<text x="%d" y="%d" style="fill:#666;font-size:%spx">%s</text>',
                    $rect['x'] + $padding + $textOffset,
                    $rect['y'] + ceil($textSize * 1.2) + $textSize + $padding,
                    ceil($textSize * .8),
                    $rect['plan']
                ) . PHP_EOL;

            $notifications = [];
            foreach ($rect['notifications'] as $days => $done) {
                $notifications[] = sprintf(
                    '<tspan style="%s">[%s %s]</tspan> ',
                    $done ? 'fill:#129900;font-weight:bold' : 'fill:#F00',
                    $days,
                    $done ? '✓' : '✕'
                );
            }

            $code .= sprintf(
                    '<text x="%d" y="%d" style="font-size:%spx">%s</text>',
                    $rect['x'] + $padding + $textOffset,
                    $rect['y'] + ceil($textSize * 1.2) + 2 * $textSize + $padding,
                    ceil($textSize * .7),
                    implode('&nbsp;', $notifications)
                ) . PHP_EOL;

            $code .= '</a>' . PHP_EOL;
        }

        $code .= '</svg>';

        return $code;
    }
}
