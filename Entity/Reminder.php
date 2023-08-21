<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Entity;

use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class Reminder
 * @package Ekyna\Bundle\SubscriptionBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method ReminderTranslation translate(string $locale = null, bool $create = false)
 */
class Reminder extends AbstractTranslatable implements ReminderInterface
{
    protected ?PlanInterface $plan    = null;
    protected ?int           $days    = null;
    protected ?string        $from    = null;
    protected ?string        $replyTo = null;
    protected bool           $enabled = false;

    public function __toString(): string
    {
        return $this->getTitle() ?? 'New reminder';
    }

    /**
     * @inheritDoc
     */
    public function getPlan(): ?PlanInterface
    {
        return $this->plan;
    }

    /**
     * @inheritDoc
     */
    public function setPlan(?PlanInterface $plan): ReminderInterface
    {
        if ($plan === $this->plan) {
            return $this;
        }

        $this->plan?->removeReminder($this);

        if ($this->plan = $plan) {
            $this->plan->addReminder($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDays(): int
    {
        return $this->days;
    }

    /**
     * @inheritDoc
     */
    public function setDays(int $days): ReminderInterface
    {
        $this->days = $days;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @inheritDoc
     */
    public function setFrom(?string $from): ReminderInterface
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    /**
     * @inheritDoc
     */
    public function setReplyTo(?string $replyTo): ReminderInterface
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @inheritDoc
     */
    public function setEnabled(bool $enabled): ReminderInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->translate()?->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getContent(): ?string
    {
        return $this->translate()?->getContent();
    }
}
