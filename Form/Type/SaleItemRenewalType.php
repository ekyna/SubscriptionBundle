<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Ekyna\Bundle\UiBundle\Form\DataTransformer\StringToDateRangeTransformer;
use Ekyna\Bundle\UiBundle\Form\Type\DateRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemRenewalType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemRenewalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new StringToDateRangeTransformer());
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->parent->vars['inner_extra_fields'][] = $form->getName();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'attr'  => [
                'label_col'  => 0,
                'widget_col' => 12,
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_subscription_sale_item_renewal';
    }

    public function getParent(): ?string
    {
        return DateRangeType::class;
    }
}
