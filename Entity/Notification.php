<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use DateTimeInterface;

/**
 * Class Notification
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Notification
{
    private ?int               $id         = null;
    private ?Reminder          $reminder   = null;
    private ?Renewal           $renewal    = null;
    private ?DateTimeInterface $notifiedAt = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Reminder|null
     */
    public function getReminder(): ?Reminder
    {
        return $this->reminder;
    }

    /**
     * @param Reminder|null $reminder
     * @return Notification
     */
    public function setReminder(?Reminder $reminder): Notification
    {
        $this->reminder = $reminder;

        return $this;
    }

    /**
     * @return Renewal|null
     */
    public function getRenewal(): ?Renewal
    {
        return $this->renewal;
    }

    /**
     * @param Renewal|null $renewal
     * @return Notification
     */
    public function setRenewal(?Renewal $renewal): Notification
    {
        if ($this->renewal === $renewal) {
            return $this;
        }

        $this->renewal?->removeNotification($this);

        $this->renewal = $renewal;

        $this->renewal?->addNotification($this);

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getNotifiedAt(): ?DateTimeInterface
    {
        return $this->notifiedAt;
    }

    /**
     * @param DateTimeInterface|null $notifiedAt
     * @return Notification
     */
    public function setNotifiedAt(?DateTimeInterface $notifiedAt): Notification
    {
        $this->notifiedAt = $notifiedAt;

        return $this;
    }
}
