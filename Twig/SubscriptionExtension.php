<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Twig;

use Ekyna\Bundle\SubscriptionBundle\Service\ConstantsHelper;
use Ekyna\Bundle\SubscriptionBundle\Service\NotificationHelper;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class SubscriptionExtension
 * @package Ekyna\Bundle\SubscriptionBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'subscription_graph',
                [SubscriptionRenderer::class, 'renderSvgGraph'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'find_renewal_notification',
                [NotificationHelper::class, 'findRenewalNotificationByReminder']
            ),
            new TwigFunction(
                'find_subscription_notifications',
                [NotificationHelper::class, 'findSubscriptionNotifications']
            ),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'subscription_state_label',
                [ConstantsHelper::class, 'renderSubscriptionStateLabel']
            ),
            new TwigFilter(
                'subscription_state_badge',
                [ConstantsHelper::class, 'renderSubscriptionStateBadge'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
