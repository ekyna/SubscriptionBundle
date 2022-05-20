<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PlanType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PlanType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'label' => t('field.designation', [], 'EkynaUi'),
            ])
            ->add('product', ProductSearchType::class)
            ->add('initialDuration', IntegerType::class, [
                'label' => t('plan.field.initial_duration', [], 'EkynaSubscription'),
            ])
            ->add('renewalDuration', IntegerType::class, [
                'label' => t('plan.field.renewal_duration', [], 'EkynaSubscription'),
            ]);
    }
}
