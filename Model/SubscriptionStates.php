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
    const STATE_NEW     = 'new';
    const STATE_PENDING = 'pending';
    const STATE_VALID   = 'valid';
    const STATE_EXEMPT  = 'exempt';

    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_subscription.subscription.state.';
        return array(
            self::STATE_NEW     => array($prefix.self::STATE_NEW,     'danger'),
            self::STATE_PENDING => array($prefix.self::STATE_PENDING, 'warning'),
            self::STATE_VALID   => array($prefix.self::STATE_VALID,   'success'),
            self::STATE_EXEMPT  => array($prefix.self::STATE_EXEMPT,  'default'),
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
            $prefix.self::STATE_NEW     => self::STATE_NEW,
            $prefix.self::STATE_PENDING => self::STATE_PENDING,
            $prefix.self::STATE_VALID   => self::STATE_VALID,
            $prefix.self::STATE_EXEMPT  => self::STATE_EXEMPT,
        );
    }
}
