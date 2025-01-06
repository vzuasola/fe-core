<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class GeolocationFetcher extends AbstractFetcher
{
    /**
     * Public constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get list of restricted countries
     */
    public function getRestrictedCountries()
    {
        $definition = $this->config->getGeneralConfigById('territory_blocking');

        return $definition->withCallback(function ($data, $options) {
            $result = [];

            if (!empty($data['territory_blocking_mapping'])) {
                $countries = explode(PHP_EOL, $data['territory_blocking_mapping']);

                foreach ($countries as $key => $value) {
                    list($id, $map) = explode('|', trim($value));
                    $result[$id] = explode(',', $map);
                }
            }

            return $result;
        });
    }
}
