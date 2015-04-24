<?php

namespace Ekyna\Bundle\SubscriptionBundle\Util;

/**
 * Class Year
 * @package Ekyna\Bundle\SubscriptionBundle\Util
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Year
{
    /**
     * Validates the year.
     *
     * @param $year
     * @return string
     * @throw GenerationException
     */
    public static function validate($year)
    {
        if (null === $year) {
            $year = date('Y');
        }

        if (!preg_match('~^19|20[0-9]{2}$~', $year)) {
            throw new \InvalidArgumentException('Invalid year argument.');
        }

        return $year;
    }
}
