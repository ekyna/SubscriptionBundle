<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

/**
 * Class ReminderRepository
 * @package Ekyna\Bundle\SubscriptionBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReminderRepository extends TranslatableRepository implements ReminderRepositoryInterface
{
    public function findEnabled(): array
    {
        return $this->findBy(['enabled' => true], ['days' => 'DESC']);
    }
}
