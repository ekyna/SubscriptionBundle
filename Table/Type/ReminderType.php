<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class ReminderType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReminderType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([100])
            ->addDefaultSort('days', ColumnSort::DESC)
            ->addDefaultSort('id', ColumnSort::DESC)
            ->addColumn('title', BType\Column\AnchorType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 0,
            ])
            ->addColumn('days', CType\Column\NumberType::class, [
                'label'     => t('reminder.field.days', [], 'EkynaSubscription'),
                'precision' => 10,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label' => t('field.enabled', [], 'EkynaUi'),
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ]);
    }
}
