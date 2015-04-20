<?php

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Component\Table\AbstractTableType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PriceType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PriceType extends AbstractTableType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * Constructor.
     *
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options = array())
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_subscription_pricing';
    }
}
