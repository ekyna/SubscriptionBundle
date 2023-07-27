<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface ReminderRepositoryInterface
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements TranslatableRepositoryInterface<ReminderInterface>
 */
interface ReminderRepositoryInterface extends TranslatableRepositoryInterface
{
    /**
     * @return array<int, ReminderInterface>
     */
    public function findEnabled(): array;
}
