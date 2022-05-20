<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type\Column\ConstantChoiceType;
use Ekyna\Bundle\AdminBundle\Table\Type\Column\ResourceType as ResourceColumn;
use Ekyna\Bundle\CommerceBundle\Table\Filter\CustomerType;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType as ResourceFilter;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\DateTimeType;
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
            ->addColumn('state', ConstantChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'class'    => SubscriptionStates::class,
                'theme'    => true,
            ])
            ->addColumn('customer', ResourceColumn::class, [
                'resource' => CustomerInterface::class,
            ])
            ->addColumn('expiresAt', DateTimeType::class, [
                'label' => t('field.expires_at', [], 'EkynaUi'),
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('customer', CustomerType::class)
            ->addFilter('plan', ResourceFilter::class, [
                'resource' => PlanInterface::class,
            ]);
    }
}
