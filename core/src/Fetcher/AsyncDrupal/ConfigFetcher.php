<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class ConfigFetcher extends AbstractFetcher
{
    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product)
    {
        parent::__construct($client, $logger, $product);

        $this->client = $client;
        $this->host = $host;
        $this->logger = $logger;
        $this->product = $product;
    }

    /**
     * Gets a configuration values by ID
     *
     * @param string $id The configuration ID
     */
    public function getConfig($id)
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        return $this->createRequest($this->client, 'GET', "$this->host/configs/view/$id", [], $callback, true);
    }

    /**
     * Gets a configuration values by ID
     *
     * @param string $id The configuration ID
     */
    public function getConfigById($id)
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        return $this->createRequest($this->client, 'GET', "$this->host/configuration/view/$id", [], $callback, true);
    }

    /**
     * Gets a general configuration values by ID
     *
     * @param string $id The configuration ID
     */
    public function getGeneralConfigById($id)
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        $url = "$this->host/configuration/general/view/$id";

        return $this->createRequest($this->client, 'GET', $url, [], $callback, true);
    }
}
