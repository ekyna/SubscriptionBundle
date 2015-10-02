<?php

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection;

use Ekyna\Bundle\AdminBundle\DependencyInjection\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $container->setParameter('ekyna_subscription.subscription.price_provider', $config['price_provider']);

        $exposedConfig = [
            'interval'  => $config['notification_interval'],
            'templates' => $config['templates']
        ];
        $container->setParameter('ekyna_subscription.config', $exposedConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        parent::prepend($container);

        $bundles = $container->getParameter('kernel.bundles');

        if (array_key_exists('TwigBundle', $bundles)) {
            $container->prependExtensionConfig('twig', array(
                'form' => array('resources' => array(
                    'EkynaSubscriptionBundle:Form:form_div_layout.html.twig'
                )),
            ));
        }
        if (array_key_exists('AsseticBundle', $bundles)) {
            $container->prependExtensionConfig('assetic', array(
                'bundles' => array('EkynaSubscriptionBundle')
            ));
        }
    }
}
