<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Ekyna\Bundle\ResourceBundle\Helper\AbstractConstantsHelper;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;

/**
 * Class ConstantsHelper
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConstantsHelper extends AbstractConstantsHelper
{

    /**
     * Renders the subscription state label.
     *
     * @param SubscriptionInterface|string $stateOrSubscription
     *
     * @return string
     */
    public function renderSubscriptionStateLabel($stateOrSubscription): string
    {
        if ($stateOrSubscription instanceof SubscriptionInterface) {
            $stateOrSubscription = $stateOrSubscription->getState();
        }

        if (SubscriptionStates::isValid($stateOrSubscription)) {
            return $this->renderLabel(SubscriptionStates::getLabel($stateOrSubscription));
        }

        return $this->renderLabel(null);
    }

    /**
     * Renders the subscription state badge.
     *
     * @param SubscriptionInterface|string $stateOrSubscription
     *
     * @return string
     */
    public function renderSubscriptionStateBadge($stateOrSubscription): string
    {
        if ($stateOrSubscription instanceof SubscriptionInterface) {
            $stateOrSubscription = $stateOrSubscription->getState();
        }

        $theme = 'default';
        if (SubscriptionStates::isValid($stateOrSubscription)) {
            $theme = SubscriptionStates::getTheme($stateOrSubscription);
        }

        return $this->renderBadge($this->renderSubscriptionStateLabel($stateOrSubscription), $theme);
    }
}
