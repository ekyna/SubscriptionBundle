<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\ReadResourceEvent;
use Ekyna\Bundle\AdminBundle\Show\Tab;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionHelper;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

use function Symfony\Component\Translation\t;

/**
 * Class ReadCustomerEventListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReadCustomerEventListener
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly SubscriptionHelper              $subscriptionHelper,
    ) {
    }

    public function __invoke(ReadResourceEvent $event): void
    {
        $customer = $event->getResource();

        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $subscriptions = $this->subscriptionRepository->findBy([
            'customer' => $customer,
        ]);

        $subscribeForm = $this->subscriptionHelper->getSubscribeForm($customer);

        $event->addTab(Tab::create(
            'subscriptions',
            t('subscription.label.plural', [], 'EkynaSubscription'),
            '@EkynaSubscription/Admin/Customer/Read/subscriptions.html.twig',
            [
                'subscriptions' => $subscriptions,
                'subscribe_form' => $subscribeForm->createView(),
            ]
        ));
    }
}
