<?php

namespace App\Utils;

use App\Kernel;

/**
 *
 */
class Url
{
    /**
     *
     */
    public static function generateFromRequest($request, $uri, $options = [])
    {
        $container = Kernel::container();

        return $container->get('uri')->generateFromRequest($request, $uri, $options);
    }

    /**
     *
     */
    public static function generateCanonicalsFromRequest($request, $uri)
    {
        $container = Kernel::container();

        return $container->get('uri')->generateCanonicalFromRequest($request, $uri);
    }

    /**
     *
     */
    public static function generateAssetUri($uri, $options = [])
    {
        $container = Kernel::container();

        return $container->get('asset')->generateAssetUri($uri, $options);
    }

    /**
     *
     */
    public static function getAliasFromUrl($url)
    {
        $container = Kernel::container();

        return $container->get('uri')->getAliasFromUrl($uri);
    }
}
