<?php

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Ekyna\Bundle\SubscriptionBundle\Entity\SubscriptionRepository;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CreateOrderType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CreateOrderType extends AbstractType
{
    /**
     * @var string
     */
    protected $subscriptionClass;


    /**
     * Constructor.
     *
     * @param string $subscriptionClass
     */
    public function __construct($subscriptionClass)
    {
        $this->subscriptionClass = $subscriptionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subscriptions', 'entity', array(
                'label' => 'ekyna_subscription.subscription.label.plural',
                'class' => $this->subscriptionClass,
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'query_builder' => function (SubscriptionRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('s');
                    return $qb
                        ->andWhere($qb->expr()->eq('s.user', ':user'))
                        ->andWhere($qb->expr()->eq('s.state', ':state'))
                        ->setParameters(array(
                            'user'  => $options['user'],
                            'state' => SubscriptionStates::STATE_NEW,
                        ))
                    ;
                },
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'user'  => null,
            ))
            ->setRequired(array('user'))
            ->setAllowedTypes(array(
                'user' => 'Ekyna\Bundle\UserBundle\Model\UserInterface',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_subscription_create_order';
    }
}
