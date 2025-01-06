<?php

namespace App\Utils;

use LayerShifter\TLDExtract\Extract;

/**
 * Class for handling host related functionalities
 */
class Host
{
    /**
     * Gets the current top level domain
     *
     * @return string
     */
    public static function getDomain()
    {
        $host = $_SERVER['HTTP_HOST'] ?? null;

        if ($host) {
            $extract = new Extract();
            $result = $extract->parse($host);
            $domain = $result->getRegistrableDomain();

            return $domain;
        }
    }

    /**
     * Gets the current host name
     *
     * @return string
     */
    public static function getHostname()
    {
        $host = $_SERVER['HTTP_HOST'];

        $extract = new Extract();
        $result = $extract->parse($host);
        $domain = $result->getFullHost();

        return $domain;
    }

    /**
     * Gets the top level domain of a specified uri
     *
     * @param string $uri
     *
     * @return string
     */
    public static function getDomainFromUri($uri)
    {
        $extract = new Extract();
        $result = $extract->parse($uri);
        $domain = $result->getRegistrableDomain();

        return $domain;
    }

    /**
     *
     */
    public static function getHostnameFromUri($uri)
    {
        $extract = new Extract();
        $result = $extract->parse($uri);
        $domain = $result->getFullHost();

        return $domain;
    }
}
