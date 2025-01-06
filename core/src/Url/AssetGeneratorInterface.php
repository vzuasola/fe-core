<?php

namespace App\Url;

interface AssetGeneratorInterface
{
    /**
     * Generate an asset path from the provided asset resource
     *
     * @param string $uri The asset resource
     * @param array $options Additional options
     *
     * @return string
     */
    public function generateAssetUri($uri);
}
