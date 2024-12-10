<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle;

use Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler\AdminMenuPass;
use Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler\MigrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaSubscriptionBundle
 * @package Ekyna\Bundle\SubscriptionBundle
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaSubscriptionBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminMenuPass());
        //$container->addCompilerPass(new MigrationPass());
    }
}
