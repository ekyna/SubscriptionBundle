<?php

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Ekyna\Bundle\SubscriptionBundle\Entity\SubscriptionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PaymentType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var string
     */
    protected $subscriptionClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $subscriptionClass
     */
    public function __construct($dataClass, $subscriptionClass)
    {
        $this->dataClass = $dataClass;
        $this->subscriptionClass = $subscriptionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = array())
    {
        if (null !== $user = $options['user']) {
            $builder
                ->add('subscriptions', 'entity', array(
                    'label' => 'ekyna_subscription.subscription.label.plural',
                    'class' => $this->subscriptionClass,
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true,
                    'query_builder' => function (SubscriptionRepository $er) use ($user) {
                        return $er->createFindByUserAndPaymentRequiredQueryBuilder($user);
                    },
                ))
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'class' => $this->dataClass,
                'user'  => null,
            ))
            ->setAllowedTypes(array(
                'user' => array('null', 'Ekyna\Bundle\UserBundle\Model\UserInterface')
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'ekyna_payment_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_subscription_payment';
    }
}
