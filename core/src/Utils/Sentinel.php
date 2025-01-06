<?php

namespace App\Utils;

/**
 * Utility class to resolve sentinel instances from a single line query string
 */
class Sentinel
{
    /**
     * The default prefix for sentinels
     */
    const DEFAULT_PREFIX = 'tcp://';

    /**
     * Resolves an array of sentinels from a space delimited redis host names
     *
     * @param string $string
     * @param string $prefix If specified will prepend this prefix to this host
     *
     * @return array
     */
    public static function resolve($servers, $prefix = Sentinel::DEFAULT_PREFIX)
    {
        $sentinels = explode(' ', $servers);

        if (is_array($sentinels) && !empty($sentinels)) {
            array_walk($sentinels, function (&$sentinel) use ($prefix) {
                $sentinel = "$prefix$sentinel";
            });

            return $sentinels;
        }
    }
}
