<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MigrationPass
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MigrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.migration.sale_item_description')
            ->addMethodCall('addConverter', [
                new Reference('ekyna_subscription.migration.description_converter')
            ]);
    }
}
