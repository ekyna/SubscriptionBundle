<?php

namespace Ekyna\Bundle\SubscriptionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'ekyna_subscription.pricing.field.prices',
            'type' => 'ekyna_subscription_price',
        ]);
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
