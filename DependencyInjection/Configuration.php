<?php

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_subscription');

        $rootNode
            ->children()
                ->integerNode('notification_interval')
                    ->defaultValue(30*6)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('price_provider')
                    ->defaultValue('ekyna_subscription.subscription.group_price_provider')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('call_user_for_payment')
                            ->defaultValue('EkynaSubscriptionBundle:Email:call_user_for_payment.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $this->addPoolsSection($rootNode);

        return $treeBuilder;
    }

	/**
     * Adds admin pool sections.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addPoolsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('pools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('pricing')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue(array(
                                    '_form.html' => 'EkynaSubscriptionBundle:Admin/Pricing:_form.html',
                                    'list.html'  => 'EkynaSubscriptionBundle:Admin/Pricing:list.html',
                                    'show.html'  => 'EkynaSubscriptionBundle:Admin/Pricing:show.html',
                                ))->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\SubscriptionBundle\Entity\Pricing')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\SubscriptionBundle\Controller\Admin\PricingController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\SubscriptionBundle\Entity\PricingRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\SubscriptionBundle\Form\Type\PricingType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\SubscriptionBundle\Table\Type\PricingType')->end()
                                ->scalarNode('Pricing')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
