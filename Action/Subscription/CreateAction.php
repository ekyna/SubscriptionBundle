<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Action\Subscription;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\SubscriptionBundle\Model\Subscribe;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionHelper;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\SubscriptionBundle\Action\Subscription
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction
{
    public function __construct(
        private readonly SubscriptionHelper              $subscriptionHelper,
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    protected function onInit(): ?Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof SubscriptionInterface) {
            throw new UnexpectedTypeException($resource, SubscriptionInterface::class);
        }

        $form = $this->subscriptionHelper->getSubscribeForm();

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Subscribe $data */
            $data = $form->getData();
            $customer = $data->getCustomer();
            $plan = $data->getPlan();

            if (null !== $found = $this->subscriptionRepository->findOneByPlanAndCustomer($plan, $customer)) {
                return $this->redirect($this->generateResourcePath($found));
            }

            $resource
                ->setCustomer($customer)
                ->setPlan($plan);
        }

        return parent::onInit();
    }
}
