<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\FormStaticControlType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ReminderType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReminderType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plan', FormStaticControlType::class, [
                'label' => t('plan.label.singular', [], 'EkynaSubscription'),
            ])
            ->add('days', IntegerType::class, [
                'label' => t('reminder.field.days', [], 'EkynaSubscription'),
            ])
            ->add('from', EmailType::class, [
                'label'    => t('email.from', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('replyTo', EmailType::class, [
                'label'    => t('email.reply_to', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => ReminderTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ]);
    }
}
