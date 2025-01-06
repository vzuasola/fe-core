<?php

namespace App\Utils;

/**
 * Class for handling sort related functionalities
 */
class Sort
{
    /**
     * Sort base on array sub index
     *
     * @return boolean
     */
    public static function sort($a, $b)
    {
        if ($a['weight'] == $b['weight']) {
            return 0;
        }

        return ($a['weight'] < $b['weight']) ? -1 : 1;
    }
}
