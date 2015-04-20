<?php

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection;

use Ekyna\Bundle\AdminBundle\DependencyInjection\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class EkynaSubscriptionExtension
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaSubscriptionExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->configure($configs, 'ekyna_subscription', new Configuration(), $container);

        $this->configurePricing($container, $config['pricing']);
    }

    /**
     * Configures the pricing services.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configurePricing(ContainerBuilder $container, array $config)
    {
        if (!$container->hasDefinition($config['provider'])) {
            throw new ServiceNotFoundException($config['provider']);
        }

        $providerDef = $container->getDefinition($config['provider']);

        // Pricing repository
        $container
            ->getDefinition('ekyna_subscription.pricing.repository')
            ->addMethodCall('setProvider', array($providerDef))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        parent::prepend($container);

        $bundles = $container->getParameter('kernel.bundles');

        if (array_key_exists('TwigBundle', $bundles)) {
            $this->configureTwigBundle($container);
        }
    }

    /**
     * Configures the TwigBundle.
     *
     * @param ContainerBuilder $container
     */
    protected function configureTwigBundle(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', array(
            'form' => array('resources' => array('EkynaSubscriptionBundle:Form:form_div_layout.html.twig')),
        ));
    }
}
