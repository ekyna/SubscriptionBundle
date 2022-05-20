<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type\Column\ResourceType as ResourceColumn;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\BooleanType;
use Ekyna\Component\Table\Extension\Core\Type\Column\DateTimeType;
use Ekyna\Component\Table\Extension\Core\Type\Column\NumberType;
use Ekyna\Component\Table\TableBuilderInterface;

use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class RenewalType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->setSortable(false)
            ->addDefaultSort('startsAt', ColumnSort::DESC)
            ->addDefaultSort('endsAt', ColumnSort::DESC)
            ->addDefaultSort('id', ColumnSort::DESC)
            ->addColumn('order', ResourceColumn::class, [
                'property_path'    => 'orderItem.rootSale',
                'resource'         => OrderInterface::class,
                'summary'          => true,
                'summary_as_panel' => true,
            ])
            ->addColumn('startsAt', DateTimeType::class, [
                'label'       => t('field.start_date', [], 'EkynaUi'),
                'time_format' => 'none',
            ])
            ->addColumn('endsAt', DateTimeType::class, [
                'label'       => t('field.end_date', [], 'EkynaUi'),
                'time_format' => 'none',
            ])
            ->addColumn('count', NumberType::class, [
                'label'     => t('field.quantity', [], 'EkynaUi'),
                'precision' => 0,
            ])
            ->addColumn('paid', BooleanType::class, [
                'label' => t('renewal.field.paid', [], 'EkynaSubscription'),
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ]);
    }
}
