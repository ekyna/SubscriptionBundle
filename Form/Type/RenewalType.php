<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\UiBundle\Form\Type\FormStaticControlType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class RenewalType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subscription', FormStaticControlType::class, [
                'label' => t('subscription.label.singular', [], 'EkynaSubscription'),
            ])
            ->add('paid', CheckboxType::class, [
                'label'    => t('renewal.field.paid', [], 'EkynaSubscription'),
                'required' => false,
                'disabled' => true,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var RenewalInterface $renewal */
            $renewal = $event->getData();
            $form = $event->getForm();

            $disabled = null !== $renewal->getOrderItem();

            $form
                ->add('startsAt', DateType::class, [
                    'label'    => t('field.start_date', [], 'EkynaUi'),
                    'disabled' => $disabled && !$renewal->isNeedsReview(),
                ])
                ->add('endsAt', DateType::class, [
                    'label'    => t('field.end_date', [], 'EkynaUi'),
                    'disabled' => $disabled && !$renewal->isNeedsReview(),
                ])
                ->add('count', IntegerType::class, [
                    'label'    => t('field.quantity', [], 'EkynaUi'),
                    'disabled' => $disabled,
                ]);

            if ($renewal->isNeedsReview()) {
                $form->add('paid', CheckboxType::class, [
                    'label'    => t('renewal.field.needs_review', [], 'EkynaSubscription'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);
            }
        });
    }
}
