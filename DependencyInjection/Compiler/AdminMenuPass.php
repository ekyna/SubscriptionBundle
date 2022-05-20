<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\AdminBundle\Service\Menu\PoolHelper;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    private const NAME = 'subscription';

    public const  GROUP = [
        'name'     => self::NAME,
        'label'    => 'label',
        'domain'   => 'EkynaSubscription',
        'icon'     => 'calendar',
        'position' => 15,
    ];

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $helper = new PoolHelper(
            $container->getDefinition('ekyna_admin.menu.pool')
        );

        $helper
            ->addGroup(self::GROUP)
            ->addEntry([
                'name'     => 'plan',
                'resource' => PlanInterface::class,
                'position' => 1,
            ])
            ->addEntry([
                'name'     => 'subscription',
                'resource' => SubscriptionInterface::class,
                'position' => 2,
            ]);
    }
}
