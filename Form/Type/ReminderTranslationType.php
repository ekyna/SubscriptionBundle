<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Ekyna\Bundle\SubscriptionBundle\Entity\ReminderTranslation;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ReminderTranslationType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReminderTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('content', TinymceType::class, [
                'label'     => t('field.content', [], 'EkynaUi'),
                'theme'     => 'simple',
                'required'  => false,
                'help'      => t('reminder.help.content', [], 'EkynaSubscription'),
                'help_text' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReminderTranslation::class,
        ]);
    }
}
