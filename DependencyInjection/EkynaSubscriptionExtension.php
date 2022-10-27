<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\PrependBundleConfigTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Class EkynaSubscriptionExtension
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaSubscriptionExtension extends Extension implements PrependExtensionInterface
{
    use PrependBundleConfigTrait;

    public function prepend(ContainerBuilder $container)
    {
        $this->prependBundleConfigFiles($container);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        //$config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');
    }
}
