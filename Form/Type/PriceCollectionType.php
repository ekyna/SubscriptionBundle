<?php

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PriceCollectionType
 * @package Ekyna\Bundle\SubscriptionBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PriceCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'ekyna_subscription.pricing.field.prices',
            'type' => 'ekyna_subscription_price',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_subscription_price_collection';
    }
}
