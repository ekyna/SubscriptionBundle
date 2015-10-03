<?php

namespace Ekyna\Bundle\SubscriptionBundle\Twig;

use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SubscriptionExtension
 * @package Ekyna\Bundle\SubscriptionBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return SubscriptionStates::getGlobals();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'subscription_state_label',
                array($this, 'getSubscriptionStateLabel'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFilter(
                'subscription_state_badge',
                array($this, 'getSubscriptionStateBadge'),
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * Returns the subscription state translated label.
     *
     * @param mixed $subscriptionOrState
     * @return string
     */
    public function getSubscriptionStateLabel($subscriptionOrState)
    {
        $state = $subscriptionOrState instanceof SubscriptionInterface
            ? $subscriptionOrState->getState()
            : $subscriptionOrState;

        return $this->translator->trans(SubscriptionStates::getLabel($state));
    }

    /**
     * Renders the subscription state badge.
     *
     * @param mixed $subscriptionOrState
     * @return string
     */
    public function getSubscriptionStateBadge($subscriptionOrState)
    {
        $state = $subscriptionOrState instanceof SubscriptionInterface
            ? $subscriptionOrState->getState()
            : $subscriptionOrState;

        return sprintf(
            '<span class="label label-%s">%s</span>',
            SubscriptionStates::getTheme($state),
            $this->getSubscriptionStateLabel($state)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_subscription';
    }
}
