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
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render_subscription_state',  array($this, 'renderSubscriptionState'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_subscription_state',  array($this, 'getSubscriptionState'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders the subscription state.
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function renderSubscriptionState(SubscriptionInterface $subscription)
    {
        $state = $subscription->getState();
        return sprintf(
            '<span class="label label-%s">%s</span>',
            SubscriptionStates::getTheme($state),
            $this->translator->trans(SubscriptionStates::getLabel($state))
        );
    }

    /**
     * Returns the subscription state.
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getSubscriptionState(SubscriptionInterface $subscription)
    {
        return $this->translator->trans(SubscriptionStates::getLabel($subscription->getState()));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_subscription';
    }
}
