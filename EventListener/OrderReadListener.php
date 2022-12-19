<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\ReadResourceEvent;
use Ekyna\Bundle\AdminBundle\Show\Tab;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

use function Symfony\Component\Translation\t;

/**
 * Class OrderReadListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderReadListener
{
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function __invoke(ReadResourceEvent $event): void
    {
        $order = $event->getResource();

        if (!$order instanceof OrderInterface) {
            throw new UnexpectedTypeException($order, OrderInterface::class);
        }

        $subscriptions = $this->subscriptionRepository->findByOrder($order);

        if (empty($subscriptions)) {
            return;
        }

        $event->addTab(Tab::create(
            'subscriptions',
            t('subscription.label.plural', [], 'EkynaSubscription'),
            '@EkynaSubscription/Admin/Order/read_subscriptions.html.twig',
            [
                'order'         => $order,
                'subscriptions' => $subscriptions,
            ]
        ));
    }
}
