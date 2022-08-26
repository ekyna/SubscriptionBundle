<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Action\Renewal;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalHelper;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\SubscriptionBundle\Action\Renewal
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction
{
    public function __construct(
        private readonly RenewalHelper $renewalHelper,
    ) {
    }

    protected function doPersist(): ResourceEventInterface
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof RenewalInterface) {
            throw new UnexpectedTypeException($resource, RenewalInterface::class);
        }

        $order = $this->renewalHelper->renew($resource);

        $subscription = $resource->getSubscription();
        if (null === $resource->getSubscription()->getId()) {
            $this->getManager($subscription)->persist($subscription);
        }

        $this->getManager($order)->persist($order);

        return $this->getManager()->save($resource);
    }

    protected function onPostPersist(): ?Response
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof RenewalInterface) {
            throw new UnexpectedTypeException($resource, RenewalInterface::class);
        }

        return $this->redirect($this->generateResourcePath($resource->getOrder()));
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'subscription_renewal_create',
            'options' => [
                'template'      => '@EkynaSubscription/Admin/Renewal/create.html.twig',
                'form_template' => '@EkynaSubscription/Admin/Renewal/_form.html.twig',
            ],
        ]);
    }
}
