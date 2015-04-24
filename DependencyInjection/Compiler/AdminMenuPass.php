<?php

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        $pool->addMethodCall('createGroup', array(array(
            'name'     => 'users',
            'label'    => 'ekyna_user.user.label.plural',
            'icon'     => 'users',
            'position' => 99,
        )));
        $pool->addMethodCall('createEntry', array('users', array(
            'name'     => 'pricing',
            'route'    => 'ekyna_subscription_pricing_admin_home',
            'label'    => 'ekyna_subscription.label',
            'resource' => 'ekyna_subscription_pricing',
            'position' => 10,
        )));
    }
}
