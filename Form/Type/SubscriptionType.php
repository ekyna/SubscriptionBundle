<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionUtils;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class SubscriptionType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label'    => t('field.description', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('autoNotify', CheckboxType::class, [
                'label'    => t('sale.field.auto_notify', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $subscription = $event->getData();
            $form = $event->getForm();

            $disabled = SubscriptionUtils::isLocked($subscription);

            $form
                ->add('plan', ResourceChoiceType::class, [
                    'resource' => PlanInterface::class,
                    'disabled' => $disabled,
                ])
                ->add('customer', CustomerSearchType::class, [
                    'disabled' => $disabled,
                ]);
        });
    }
}
