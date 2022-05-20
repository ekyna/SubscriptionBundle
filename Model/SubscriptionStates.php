<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class SubscriptionStates
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionStates extends AbstractConstants
{
    public const STATE_NEW       = 'new';
    public const STATE_PENDING   = 'pending';
    public const STATE_RENEWED   = 'renewed';
    public const STATE_EXPIRED   = 'expired';
    public const STATE_CANCELLED = 'cancelled';

    public static function getConfig(): array
    {
        $prefix = 'subscription.state.';

        return [
            self::STATE_NEW       => [$prefix . self::STATE_NEW, 'danger'],
            self::STATE_PENDING   => [$prefix . self::STATE_PENDING, 'warning'],
            self::STATE_RENEWED   => [$prefix . self::STATE_RENEWED, 'success'],
            self::STATE_EXPIRED   => [$prefix . self::STATE_EXPIRED, 'danger'],
            self::STATE_CANCELLED => [$prefix . self::STATE_CANCELLED, 'default'],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaSubscription';
    }
}
