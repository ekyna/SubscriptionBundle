<?php

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Ekyna\Bundle\UserBundle\Entity\Group;

/**
 * Class GroupPrice
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GroupPrice extends AbstractPrice
{
    /**
     * @var Group
     */
    protected $group;


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return (string) $this->group;
    }

    /**
     * Returns the group.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the group.
     *
     * @param Group $group
     * @return GroupPrice
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;
        return $this;
    }
}
