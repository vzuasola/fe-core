<?php

namespace App\Utils;

/**
 * Class for handling string related functionality
 */
class Strings
{
    /**
     *
     */
    public static function asciiSum($string)
    {
        $sum = 0;
        $strArray = str_split($string);

        foreach ($strArray as $value) {
            $sum += ord($value);
        }

        return $sum;
    }
}
