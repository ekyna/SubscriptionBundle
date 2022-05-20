<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ekyna_subscription');

        $node = $builder->getRootNode();

        $node
            ->children()
                /*->integerNode('notification_interval')
                    ->defaultValue(30*6)
                    ->min(0)
                ->end()
                ->scalarNode('price_provider')
                    ->defaultValue('ekyna_subscription.subscription.group_price_provider')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_call_for_payment')
                            ->defaultValue('EkynaSubscriptionBundle:Email:user_call_for_payment.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()*/
            ->end()
        ;

        return $builder;
    }
}
