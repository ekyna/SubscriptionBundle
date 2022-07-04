<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Event\AdminReadEvents;
use Ekyna\Bundle\SubscriptionBundle\Action\Renewal\CreateAction;
use Ekyna\Bundle\SubscriptionBundle\Command\GenerateSubscriptionCommand;
use Ekyna\Bundle\SubscriptionBundle\Command\WatchSubscriptionCommand;
use Ekyna\Bundle\SubscriptionBundle\Event\RenewalEvents;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\EventListener\OrderItemListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\OrderListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\ReadCustomerEventListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\ReadOrderEventListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\RenewalListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\SubscriptionListener;
use Ekyna\Bundle\SubscriptionBundle\Factory\RenewalFactory;
use Ekyna\Bundle\SubscriptionBundle\MessageHandler\OrderItemAddHandler;
use Ekyna\Bundle\SubscriptionBundle\MessageHandler\OrderItemQuantityChangeHandler;
use Ekyna\Bundle\SubscriptionBundle\MessageHandler\OrderStateChangeHandler;
use Ekyna\Bundle\SubscriptionBundle\Service\ConstantsHelper;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalHelper;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalUpdater;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionGenerator;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionRenderer;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionStateResolver;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionUpdater;
use Ekyna\Bundle\SubscriptionBundle\Twig\SubscriptionExtension;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Event\OrderItemEvents;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Renewal create action
        ->set('ekyna_subscription.action.renewal.create', CreateAction::class)
            ->args([
                service('ekyna_subscription.helper.renewal'),
            ])
            ->tag('ekyna_resource.action')

        // Subscription state resolver
        ->set('ekyna_subscription.resolver.subscription_state', SubscriptionStateResolver::class)

        // Subscription updater
        ->set('ekyna_subscription.updater.subscription', SubscriptionUpdater::class)
            ->args([
                service('ekyna_subscription.resolver.subscription_state'),
            ])

        // Renewal updater
        ->set('ekyna_subscription.updater.renewal', RenewalUpdater::class)

        // Renewal factory
        ->set('ekyna_subscription.factory.renewal', RenewalFactory::class)
            ->args([
                service('ekyna_subscription.updater.renewal'),
            ])

        // Subscription generator
        ->set('ekyna_subscription.generator.subscription', SubscriptionGenerator::class)
            ->args([
                service('ekyna_subscription.repository.plan'),
                service('ekyna_subscription.repository.subscription'),
                service('ekyna_subscription.factory.subscription'),
                service('ekyna_subscription.factory.renewal'),
                service('ekyna_subscription.updater.renewal'),
            ])

        // Constants helper
        ->set('ekyna_subscription.helper.constants', ConstantsHelper::class)
            ->args([
                service('translator'),
            ])
            ->tag('twig.runtime')

        // Renewal helper
        ->set('ekyna_subscription.helper.renewal', RenewalHelper::class)
            ->args([
                service('ekyna_commerce.factory.order'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.helper.sale_item'),
            ])

        // Order item add message handler
        ->set('ekyna_subscription.message_handler.order_item_add', OrderItemAddHandler::class)
            ->args([
                service('ekyna_commerce.repository.order_item'),
                service('ekyna_subscription.repository.plan'),
                service('ekyna_subscription.generator.subscription'),
                service('ekyna_subscription.manager.subscription'),
            ])
            ->tag('messenger.message_handler')

        // Order item quantity change message handler
        ->set('ekyna_subscription.message_handler.order_item_quantity_change', OrderItemQuantityChangeHandler::class)
            ->args([
                service('ekyna_subscription.repository.renewal'),
                service('ekyna_subscription.manager.renewal'),
            ])
            ->tag('messenger.message_handler')

        // Order state change message handler
        ->set('ekyna_subscription.message_handler.order_state_change', OrderStateChangeHandler::class)
            ->args([
                service('ekyna_commerce.repository.order'),
                service('ekyna_subscription.generator.subscription'),
                service('ekyna_subscription.manager.subscription'),
            ])
            ->tag('messenger.message_handler')

        // Generate subscription command
        ->set('ekyna_subscription.command.generate', GenerateSubscriptionCommand::class)
            ->args([
                service('ekyna_commerce.repository.order'),
                service('ekyna_subscription.generator.subscription'),
                service('ekyna_subscription.manager.subscription'),
                service('database_connection'),
            ])
            ->tag('console.command')

        // Watch subscriptions command
        ->set('ekyna_subscription.command.watch', WatchSubscriptionCommand::class)
            ->args([
                service('ekyna_subscription.repository.subscription'),
                service('ekyna_subscription.manager.subscription'),
            ])
            ->tag('console.command')

        // Subscription (resource) event listener
        ->set('ekyna_subscription.listener.subscription', SubscriptionListener::class)
            ->args([
                service('ekyna_subscription.updater.subscription'),
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_listener', [
                'event'  => SubscriptionEvents::INSERT,
                'method' => 'onInsert',
            ])
            ->tag('resource.event_listener', [
                'event'  => SubscriptionEvents::RENEWAL_CHANGE,
                'method' => 'onRenewalChange',
            ])

        // Order (resource) event listener
        ->set('ekyna_subscription.listener.order', OrderListener::class)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setMessageQueue', [service('ekyna_resource.queue.message')])
            ->tag('resource.event_listener', [
                'event'  => OrderEvents::STATE_CHANGE,
                'method' => 'onStateChange',
            ])

        // Order item (resource) event listener
        ->set('ekyna_subscription.listener.order_item', OrderItemListener::class)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setMessageQueue', [service('ekyna_resource.queue.message')])
            ->tag('resource.event_listener', [
                'event'  => OrderItemEvents::INSERT,
                'method' => 'onInsert',
            ])
            ->tag('resource.event_listener', [
                'event'  => OrderItemEvents::UPDATE,
                'method' => 'onUpdate',
            ])

        // Renewal (resource) event listener
        ->set('ekyna_subscription.listener.renewal', RenewalListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_subscription.updater.renewal'),
            ])
            ->tag('resource.event_listener', [
                'event'  => RenewalEvents::INSERT,
                'method' => 'onInsert',
            ])
            ->tag('resource.event_listener', [
                'event'  => RenewalEvents::UPDATE,
                'method' => 'onUpdate',
            ])
            ->tag('resource.event_listener', [
                'event'  => RenewalEvents::DELETE,
                'method' => 'onDelete',
            ])

        // Admin customer read event listener
        ->set('ekyna_subscription.listener.admin_read_customer', ReadCustomerEventListener::class)
            ->args([
                service('ekyna_subscription.repository.subscription'),
            ])
            ->tag('kernel.event_listener', [
                'event'  => AdminReadEvents::CUSTOMER,
            ])

        // Admin order read event listener
        ->set('ekyna_subscription.listener.admin_read_order', ReadOrderEventListener::class)
            ->args([
                service('ekyna_subscription.repository.subscription'),
            ])
            ->tag('kernel.event_listener', [
                'event'  => AdminReadEvents::ORDER,
            ])

        // Subscription renderer
        ->set('ekyna_subscription.renderer.subscription', SubscriptionRenderer::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('twig.runtime')

        // Twig subscription extension
        ->set('ekyna_subscription.twig.extension.subscription', SubscriptionExtension::class)
            ->tag('twig.extension')
    ;
};
