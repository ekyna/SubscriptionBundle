<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PlanType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PlanType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('designation', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('designation', CType\Filter\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 10,
            ])
        ;
    }
}
