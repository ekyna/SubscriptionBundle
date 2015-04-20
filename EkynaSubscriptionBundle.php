<?php

namespace Ekyna\Bundle\SubscriptionBundle;

use Ekyna\Bundle\SubscriptionBundle\DependencyInjection\Compiler\AdminMenuPass;
use Ekyna\Bundle\CoreBundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaSubscriptionBundle
 * @package Ekyna\Bundle\SubscriptionBundle
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaSubscriptionBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AdminMenuPass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelInterfaces()
    {
        return array(
            'Ekyna\Bundle\SubscriptionBundle\Model\PricingInterface' => 'ekyna_subscription.pricing.class',
            'Ekyna\Bundle\SubscriptionBundle\Model\PriceInterface'   => 'ekyna_subscription.price.class',
        );
    }
}
