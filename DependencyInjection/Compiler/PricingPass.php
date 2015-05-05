<?php

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class PricingPass
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PricingPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $providerKey = $container->getParameter('ekyna_subscription.subscription.price_provider');

        if (!$container->hasDefinition($providerKey)) {
            throw new ServiceNotFoundException($providerKey);
        }
        $providerDef = $container->getDefinition($providerKey);

        // Pricing repository
        $container
            ->getDefinition('ekyna_subscription.pricing.repository')
            ->addMethodCall('setPriceProvider', array($providerDef))
        ;

        // Subscription generator
        $container
            ->getDefinition('ekyna_subscription.subscription.generator')
            ->addMethodCall('setPriceProvider', array($providerDef))
        ;
    }
}
