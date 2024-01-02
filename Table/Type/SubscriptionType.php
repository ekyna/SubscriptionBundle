<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type as AType;
use Ekyna\Bundle\CommerceBundle\Table\Filter\CustomerType;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType as ResourceFilter;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\SubscriptionBundle\Table\Column\SubscriptionExpiresAtType;
use Ekyna\Bundle\SubscriptionBundle\Table\Column\SubscriptionRemindersType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SubscriptionType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'         => t('field.name', [], 'EkynaUi'),
                'property_path' => false,
            ])
            ->addColumn('state', AType\Column\ConstantChoiceType::class, [
                'label' => t('field.status', [], 'EkynaUi'),
                'class' => SubscriptionStates::class,
                'theme' => true,
            ])
            ->addColumn('customer', AType\Column\ResourceType::class, [
                'resource' => CustomerInterface::class,
            ])
            ->addColumn('autoNotify', CType\Column\BooleanType::class, [
                'label' => t('sale.field.auto_notify', [], 'EkynaCommerce'),
            ])
            ->addColumn('reminders', SubscriptionRemindersType::class)
            ->addColumn('expiresAt', SubscriptionExpiresAtType::class)
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ])
            ->addFilter('customer', CustomerType::class)
            ->addFilter('plan', ResourceFilter::class, [
                'resource' => PlanInterface::class,
            ])
            ->addFilter('state', AType\Filter\ConstantChoiceType::class, [
                'label' => t('field.status', [], 'EkynaUi'),
                'class' => SubscriptionStates::class,
            ])
            ->addFilter('autoNotify', CType\Filter\BooleanType::class, [
                'label' => t('sale.field.auto_notify', [], 'EkynaCommerce'),
            ])
            ->addFilter('expiresAt', CType\Filter\DateTimeType::class, [
                'label' => t('field.expires_at', [], 'EkynaUi'),
            ]);
    }
}
