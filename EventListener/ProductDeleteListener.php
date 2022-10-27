<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\SubscriptionBundle\Repository\PlanRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class ProductDeleteListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductDeleteListener
{
    public function __construct(
        private readonly PlanRepositoryInterface $planRepository,
        private readonly ResourceHelper          $resourceHelper,
    ) {
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $product = $event->getResource();

        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        if (null === $plan = $this->planRepository->findOneByProduct($product)) {
            return;
        }

        $message = ResourceMessage::create('plan.message.product_deletion_prevented', ResourceMessage::TYPE_ERROR)
            ->setParameters([
                '{url}'         => $this->resourceHelper->generateResourcePath($plan, ReadAction::class),
                '{designation}' => $plan->getDesignation(),
            ])
            ->setDomain('EkynaSubscription');

        $event->addMessage($message);
    }
}
