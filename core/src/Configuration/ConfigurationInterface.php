<?php

namespace App\Configuration;

/**
 * Interface for defining how application configurations should be fetched
 */
interface ConfigurationInterface
{
    /**
     * Gets a system configuration
     *
     * @param string $filename The configuration file name without extension
     *
     * @return array
     */
    public function getConfiguration($filename);
}
