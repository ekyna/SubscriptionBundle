<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Action\Subscription;

use Ekyna\Bundle\AdminBundle\Action\AbstractConfirmAction;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

use function Symfony\Component\Translation\t;

/**
 * Class CancelAction
 * @package Ekyna\Bundle\SubscriptionBundle\Action\Subscription
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CancelAction extends AbstractConfirmAction
{
    protected function doPersist(): ResourceEventInterface
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof SubscriptionInterface) {
            throw new UnexpectedTypeException($resource, SubscriptionInterface::class);
        }

        $resource->setState(SubscriptionStates::STATE_CANCELLED);

        return $this->getManager()->save($resource);
    }

    protected function getFormOptions(): array
    {
        return [
            'message' => t('subscription.message.cancel_confirm', [], 'EkynaSubscription'),
        ];
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'subscription_subscription_cancel',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_cancel',
                'path'     => '/cancel',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label' => 'button.cancel',
                'theme' => 'danger',
                'icon'  => 'remove',
            ],
            'options'    => [
                'template'      => '@EkynaSubscription/Admin/Subscription/cancel.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_confirm.html.twig',
                //'serialization' => ['groups' => ['Default'], 'admin' => true],
            ],
        ];
    }
}
