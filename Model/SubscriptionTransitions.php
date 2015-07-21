<?php

namespace Ekyna\Bundle\SubscriptionBundle\Model;

/**
 * Class SubscriptionTransitions
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionTransitions
{
    const TRANSITION_LOCK     = 'lock';
    const TRANSITION_VALIDATE = 'validate';
    const TRANSITION_UNLOCK   = 'unlock';
    const TRANSITION_EXEMPT   = 'exempt';
    const TRANSITION_UNEXEMPT = 'unexempt';
}
