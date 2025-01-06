<?php

namespace App\Configuration;

use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class YamlConfiguration implements ConfigurationInterface
{
    private $cache = [];

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Gets a system configuration
     *
     * @param string $filename The configuration file name without extension
     *
     * @return array
     */
    public function getConfiguration($filename)
    {
        $configs = [];

        if (!isset($this->cache[$filename])) {
            if ($this->settings['inheritance'] && file_exists(APP_ROOT . "/core/app/config/$filename.yml")) {
                $coreConfig = Yaml::parse(file_get_contents(APP_ROOT . "/core/app/config/$filename.yml"));

                if ($coreConfig) {
                    $configs = array_replace_recursive($configs, $coreConfig);
                }
            }

            if (isset($this->settings['brand_directories']) && !empty($this->settings['brand_directories'])) {
                foreach ($this->settings['brand_directories'] as $dir) {
                    if (file_exists("$dir/app/config/$filename.yml")) {
                        $coreConfig = Yaml::parse(file_get_contents("$dir/app/config/$filename.yml"));

                        if ($coreConfig) {
                            $configs = array_replace_recursive($configs, $coreConfig);
                        }
                    }
                }
            }

            if (file_exists(CONFIG_ROOT . "/$filename.yml")) {
                $siteConfig = Yaml::parse(file_get_contents(CONFIG_ROOT . "/$filename.yml"));

                if ($siteConfig) {
                    $configs = array_replace_recursive($configs, $siteConfig);
                }
            }

            $this->cache[$filename] = $configs;
        }

        return $this->cache[$filename];
    }
}
