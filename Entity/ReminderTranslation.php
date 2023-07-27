<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class ReminderTranslation
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReminderTranslation extends AbstractTranslation
{
    protected ?string $title   = null;
    protected ?string $content = null;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return ReminderTranslation
     */
    public function setTitle(?string $title): ReminderTranslation
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return ReminderTranslation
     */
    public function setContent(?string $content): ReminderTranslation
    {
        $this->content = $content;

        return $this;
    }
}
