<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Event\AdminReadEvents;
use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\SubscriptionBundle\Action\Renewal\CreateAction as RenewalCreateAction;
use Ekyna\Bundle\SubscriptionBundle\Action\Subscription\CreateAction as SubscriptionCreateAction;
use Ekyna\Bundle\SubscriptionBundle\Command\GenerateSubscriptionCommand;
use Ekyna\Bundle\SubscriptionBundle\Command\WatchSubscriptionCommand;
use Ekyna\Bundle\SubscriptionBundle\Event\RenewalEvents;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\EventListener\OrderItemListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\OrderListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\ProductDeleteListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\CustomerReadListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\OrderReadListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\RenewalListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\SaleItemListener;
use Ekyna\Bundle\SubscriptionBundle\EventListener\SubscriptionListener;
use Ekyna\Bundle\SubscriptionBundle\Factory\RenewalFactory;
use Ekyna\Bundle\SubscriptionBundle\MessageHandler\OrderItemAddHandler;
use Ekyna\Bundle\SubscriptionBundle\MessageHandler\OrderItemQuantityChangeHandler;
use Ekyna\Bundle\SubscriptionBundle\MessageHandler\OrderStateChangeHandler;
use Ekyna\Bundle\SubscriptionBundle\Service\ConstantsHelper;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalCalculator;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalHelper;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalUpdater;
use Ekyna\Bundle\SubscriptionBundle\Service\SaleItemUpdater;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionGenerator;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionHelper;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionRenderer;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionStateResolver;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionUpdater;
use Ekyna\Bundle\SubscriptionBundle\Twig\SubscriptionExtension;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Event\OrderItemEvents;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Renewal create action
        ->set('ekyna_subscription.action.renewal.create', RenewalCreateAction::class)
            ->args([
                service('ekyna_subscription.helper.renewal'),
            ])
            ->tag('ekyna_resource.action')

        // Subscription create action
        ->set('ekyna_subscription.action.subscription.create', SubscriptionCreateAction::class)
            ->args([
                service('ekyna_subscription.helper.subscription'),
                service('ekyna_subscription.repository.subscription'),
            ])
            ->tag('ekyna_resource.action')

        // Subscription state resolver
        ->set('ekyna_subscription.resolver.subscription_state', SubscriptionStateResolver::class)

        // Subscription updater
        ->set('ekyna_subscription.updater.subscription', SubscriptionUpdater::class)
            ->args([
                service('ekyna_subscription.resolver.subscription_state'),
            ])

        // Renewal calculator
        ->set('ekyna_subscription.calculator.renewal', RenewalCalculator::class)

        // Renewal updater
        ->set('ekyna_subscription.updater.renewal', RenewalUpdater::class)
            ->args([
                service('ekyna_subscription.calculator.renewal'),
            ])

        // Sale item updater
        ->set('ekyna_subscription.updater.sale_item', SaleItemUpdater::class)
            ->args([
                service('ekyna_commerce.provider.context'),
                service('ekyna_commerce.factory.formatter'),
                service('translator'),
            ])

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
                service('ekyna_subscription.factory.subscription'),
                service('ekyna_commerce.factory.order'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.helper.sale_item'),
                service('translator'),
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

        // Product resource delete event listener
        ->set('ekyna_subscription.listener.product.delete', ProductDeleteListener::class)
            ->args([
                service('ekyna_subscription.repository.plan'),
                service('ekyna_resource.helper'),
            ])
            ->tag('resource.event_listener', [
                'event'    => ProductEvents::PRE_DELETE,
                'method'   => 'onPreDelete',
                'priority' => 1024,
            ])

        // Renewal (resource) event listener
        ->set('ekyna_subscription.listener.renewal', RenewalListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_subscription.updater.renewal'),
                service('ekyna_subscription.updater.sale_item'),
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

        // Sale item event listener
        ->set('ekyna_subscription.listener.sale_item', SaleItemListener::class)
            ->args([
                service('ekyna_subscription.repository.plan'),
                service('ekyna_commerce.helper.subject'),
                service('ekyna_subscription.repository.renewal'),
                service('ekyna_subscription.updater.sale_item'),
                service('ekyna_subscription.calculator.renewal'),
            ])
            ->tag('kernel.event_listener', [
                'event'    => SaleItemEvents::BUILD,
                'method'   => 'onSaleItemBuild',
                'priority' => -1024,
            ])
            ->tag('kernel.event_listener', [
                'event'    => SaleItemFormEvent::BUILD_FORM,
                'method'   => 'onSaleItemBuildForm',
                'priority' => -1024,
            ])

        // Admin customer read event listener
        ->set('ekyna_subscription.listener.customer_admin_read', CustomerReadListener::class)
            ->args([
                service('ekyna_subscription.repository.subscription'),
                service('ekyna_subscription.helper.subscription'),
            ])
            ->tag('kernel.event_listener', [
                'event' => AdminReadEvents::CUSTOMER,
            ])

        // Admin order read event listener
        ->set('ekyna_subscription.listener.order_admin_read', OrderReadListener::class)
            ->args([
                service('ekyna_subscription.repository.subscription'),
            ])
            ->tag('kernel.event_listener', [
                'event' => AdminReadEvents::ORDER,
            ])

        // Subscription renderer
        ->set('ekyna_subscription.renderer.subscription', SubscriptionRenderer::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('twig.runtime')

        // Subscription helper
        ->set('ekyna_subscription.helper.subscription', SubscriptionHelper::class)
            ->args([
                service('ekyna_resource.helper'),
                service('form.factory'),
            ])

        // Twig subscription extension
        ->set('ekyna_subscription.twig.extension.subscription', SubscriptionExtension::class)
            ->tag('twig.extension')
    ;
};
