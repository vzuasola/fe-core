<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class TerritoryBlockingFetcher
{
    /**
     * The configuration of restrcited countries
     *
     * @var string
     */
    private $config;

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
     * Get total balance
     */
    public function getRestrictedCountries()
    {

        $data = $this->config->getGeneralConfigById('territory_blocking');

        $restrictedCountries = $data['territory_blocking_mapping'];
        $restrictedCountries = explode(PHP_EOL, $restrictedCountries);
        $countryList = array();

        foreach ($restrictedCountries as $key => $value) {
            list($newKey, $newValue) = explode('|', rtrim($value));
            $countryList[$newKey] = explode(',', $newValue);
        }

        return $countryList;
    }
}
