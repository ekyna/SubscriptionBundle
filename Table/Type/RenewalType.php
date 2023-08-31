<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\SubscriptionBundle\Action\Renewal\CreateAction;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type\Column\BooleanType;
use Ekyna\Component\Table\Extension\Core\Type\Column\DateTimeType;
use Ekyna\Component\Table\Extension\Core\Type\Column\NumberType;
use Ekyna\Component\Table\Source\RowInterface;
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
            ->addColumn('order', BType\Column\AnchorType::class, [
                'label'   => t('renewal.label.singular', [], 'EkynaSubscription'),
                'summary' => 'order',
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
                    CreateAction::class => [
                        'label'          => t('subscription.button.extend', [], 'EkynaSubscription'),
                        'icon'           => 'resize-vertical',
                        'theme'          => 'primary',
                        'parameters_map' => [
                            'subscriptionId' => 'subscription.id',
                            'extend'         => 'id',
                        ],
                    ],
                    UpdateAction::class,
                    DeleteAction::class => [
                        'disable' => static function (RowInterface $row): bool {
                            /** @var RenewalInterface $renewal */
                            $renewal = $row->getData(null);

                            return null !== $renewal->getOrderItem();
                        },
                    ],
                ],
            ]);
    }
}
