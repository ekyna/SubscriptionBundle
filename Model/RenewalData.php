<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Model;

use DateTimeInterface;

/**
 * Class RenewalData
 * @package Ekyna\Bundle\SubscriptionBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalData
{
    private DateTimeInterface $start;
    private DateTimeInterface $end;
    private int               $count;

    public function __construct(DateTimeInterface $start, DateTimeInterface $end, int $count)
    {
        $this->start = $start;
        $this->end = $end;
        $this->count = $count;
    }

    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): DateTimeInterface
    {
        return $this->end;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
