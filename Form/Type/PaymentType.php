<?php

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
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
     * Constructor.
     *
     * @param $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'class' => $this->dataClass,
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
