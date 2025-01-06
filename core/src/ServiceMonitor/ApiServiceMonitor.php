<?php

namespace App\ServiceMonitor;

use App\Plugins\ServiceMonitor\ServiceMonitorInterface;
use Predis\Client;

class ApiServiceMonitor implements ServiceMonitorInterface
{
    private $languages;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('language_fetcher')
        );
    }

    /**
     *
     */
    public function __construct($languages)
    {
        $this->languages = $languages;
    }

    /**
     * @{inheritdoc}
     */
    public function check()
    {
        $result = true;

        try {
            $this->languages->getLanguages();
        } catch (\Exception $e) {
            $result = "Connectivity to Drupal via CMS API cannot be established";
        }

        return $result;
    }
}
