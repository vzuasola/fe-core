<?php

namespace App\Utils;

/**
 * Date Helper Class
 */
class Date
{
    /**
     * Convert Dot Net Date
     * ex. /Date(1495500040625+0000)/ or /Date(1495500040625)/
     *
     * @param string $dotNetDate
     *
     * @return object
     */
    public static function convertDotNet($dotNetDate)
    {
        preg_match('#\([^)]+\)#', $dotNetDate, $date);
        $newDate = str_replace(array('(',')'), '', $date);

        $timestamp = self::getDotNetTimestamp($newDate[0]);

        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timestamp);

        return $dateTime;
    }

    /**
     * Convert unixstamp to timestamp (with or without UTC Timezone)
     *  - 1495500040625+0000 or 1495500040625
     *
     * @param string $dotNetDate
     *
     * @return integer
     */
    private static function getDotNetTimestamp($dotNetDate)
    {
        $withUtcTimezone = self::checkUtcTimezone($dotNetDate);
        if ($withUtcTimezone) {
            preg_match('/(-?\d+)([+-]\d{4})/', $dotNetDate, $matches);

            // microseconds to seconds
            $timestamp = $matches[1]/1000;

            // utc timezone difference in seconds
            $UTCSec = $matches[2]/100*60*60;

            //add or divide the utc timezone difference
            $timestamp += $UTCSec;
            return intval($timestamp);
        }
        // microseconds to seconds
        $timestamp = $dotNetDate / 1000;

        return intval($timestamp);
    }

    /**
     * Check if utc timezone exist
     *
     * @param string $dotNetDate
     *
     * @return boolean
     */
    private static function checkUtcTimezone($dotNetDate)
    {
        $needles = ['+', '-'];
        foreach ($needles as $needle) {
            $pos = strpos($dotNetDate, $needle);
            if ($pos !== false) {
                return $pos;
            }
        }
        return false;
    }
}
