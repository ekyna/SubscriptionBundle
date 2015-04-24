<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Ekyna\Bundle\CoreBundle\Model\AbstractConstants;

/**
 * Class SubscriptionStates
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionStates extends AbstractConstants
{
    const PENDING = 'pending';
    const PAID    = 'paid';
    const EXEMPT  = 'exempt';

    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_subscription.subscription.state.';
        return array(
            self::PENDING => array($prefix.self::PENDING, 'warning'),
            self::PAID    => array($prefix.self::PAID,    'success'),
            self::EXEMPT  => array($prefix.self::EXEMPT,  'default'),
        );
    }

    /**
     * Returns the theme for the given state.
     *
     * @param string $state
     * @return string
     */
    static public function getTheme($state)
    {
        static::isValid($state, true);

        return static::getConfig()[$state][1];
    }

    /**
     * Returns the twig globals.
     *
     * @return array
     */
    static public function getGlobals()
    {
        $prefix = 'subscription_state_';
        return array(
            $prefix.self::PENDING => self::PENDING,
            $prefix.self::PAID    => self::PAID,
            $prefix.self::EXEMPT  => self::EXEMPT,
        );
    }
}
