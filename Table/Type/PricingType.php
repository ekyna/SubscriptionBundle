<?php

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class PricingType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PricingType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('year', 'anchor', array(
                'label' => 'ekyna_subscription.pricing.field.year',
                'sortable' => true,
                'route_name' => 'ekyna_subscription_pricing_admin_show',
                'route_parameters_map' => array('pricingId' => 'id'),
            ))
            ->addColumn('actions', 'admin_actions', array(
                'buttons' => array(
                    array(
                        'label' => 'ekyna_core.button.edit',
                        'icon' => 'pencil',
                        'class' => 'warning',
                        'route_name' => 'ekyna_subscription_pricing_admin_edit',
                        'route_parameters_map' => array('pricingId' => 'id'),
                        'permission' => 'edit',
                    ),
                    array(
                        'label' => 'ekyna_core.button.remove',
                        'icon' => 'trash',
                        'class' => 'danger',
                        'route_name' => 'ekyna_subscription_pricing_admin_remove',
                        'route_parameters_map' => array('pricingId' => 'id'),
                        'permission' => 'delete',
                    ),
                ),
            ))
            ->addFilter('year', 'text', array(
                'label' => 'ekyna_subscription.pricing.field.year',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_subscription_pricing';
    }
}
