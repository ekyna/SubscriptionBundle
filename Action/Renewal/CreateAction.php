<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Action\Renewal;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalHelper;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
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
    use RepositoryTrait;

    public function __construct(
        private readonly RenewalHelper $renewalHelper,
    ) {
    }

    protected function onInit(): ?Response
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof RenewalInterface) {
            throw new UnexpectedTypeException($resource, RenewalInterface::class);
        }

        if (0 < $id = $this->request->query->getInt('extend')) {
            $extend = $this->getRepository($this->context->getConfig()->getEntityClass())->find($id);

            if ($extend instanceof RenewalInterface) {
                $resource
                    ->setStartsAt(clone $extend->getStartsAt())
                    ->setEndsAt(clone $extend->getEndsAt());
            }
        }

        return parent::onInit();
    }

    protected function doPersist(): ResourceEventInterface
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof RenewalInterface) {
            throw new UnexpectedTypeException($resource, RenewalInterface::class);
        }

        $order = $this->renewalHelper->renew($resource);

        // TODO Validate order (need addresses, etc)

        $subscription = $resource->getSubscription();
        if (null === $resource->getSubscription()->getId()) {
            $this->getManager($subscription)->persist($this->context->getParentResource());
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

    protected function buildParameters(array $extra = []): array
    {
        /** @var RenewalInterface $resource */
        $resource = $this->context->getResource();

        $renewals = $resource->getSubscription()->getRenewals()->toArray();

        $pending = false;
        foreach ($renewals as $renewal) {
            if ($renewal === $resource) {
                continue;
            }

            if ($renewal->isPaid()) {
                continue;
            }

            $pending = true;
            break;
        }

        $extra['pending_subscription'] = $pending;

        return parent::buildParameters($extra);
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
