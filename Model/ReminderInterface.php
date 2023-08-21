<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface ReminderInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ReminderInterface extends TranslatableInterface
{
    /**
     * Returns the plan this reminder applies to.
     *
     * @return PlanInterface|null
     */
    public function getPlan(): ?PlanInterface;

    /**
     * Sets the plan this reminder applies to.
     *
     * @param PlanInterface|null $plan
     * @return ReminderInterface
     */
    public function setPlan(?PlanInterface $plan): ReminderInterface;

    /**
     * Returns the number of days to remind the subscription before expiration.
     *
     * @return int
     */
    public function getDays(): int;

    /**
     * Sets the number of days to remind the subscription before expiration.
     *
     * @param int $days
     * @return ReminderInterface
     */
    public function setDays(int $days): ReminderInterface;

    /**
     * Returns the notification sender.
     *
     * @return string|null
     */
    public function getFrom(): ?string;

    /**
     * Sets the notification sender.
     *
     * @param string|null $from
     */
    public function setFrom(?string $from): ReminderInterface;

    /**
     * Returns the notification reply to.
     *
     * @return string|null
     */
    public function getReplyTo(): ?string;

    /**
     * Sets the notification reply to.
     *
     * @param string|null $replyTo
     */
    public function setReplyTo(?string $replyTo): ReminderInterface;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $enabled
     * @return ReminderInterface
     */
    public function setEnabled(bool $enabled): ReminderInterface;

    /**
     * Returns the translated reminder title.
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Returns the translated reminder content.
     *
     * @return string|null
     */
    public function getContent(): ?string;
}
