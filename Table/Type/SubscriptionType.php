<?php

namespace Ekyna\Bundle\SubscriptionBundle\Table\Type;

use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Table\AbstractTableType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SubscriptionType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionType extends AbstractTableType
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
        $builder
            ->addColumn('user', 'anchor', array(
                'label' => 'ekyna_user.user.label.singular',
                'route_name' => 'ekyna_user_user_admin_show',
                'route_parameters_map' => array('userId' => 'user.id'),
            ))
            ->addColumn('price', 'number', array(
                'label' => 'ekyna_subscription.price.field.amount',
                'property_path' => 'price.amount',
            ))
            ->addColumn('state', 'choice', array(
                'label' => 'ekyna_core.field.status',
                'choices' => SubscriptionStates::getChoices(),
            ))
            ->addColumn('notified_at', 'datetime', array(
                'label' => 'ekyna_subscription.subscription.field.notified_at',
                'time_format' => 'none',
            ))
            ->addFilter('state', 'choice', array(
                'label' => 'ekyna_core.field.status',
                'choices' => SubscriptionStates::getChoices(),
            ))
        ;
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
        return 'ekyna_subscription_subscription';
    }
}
