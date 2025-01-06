<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class ConfigFetcher extends AbstractFetcher
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product, $cacher)
    {
        parent::__construct($client, $logger, $product, $cacher);

        $this->host = $host;
    }

    /**
     * Gets a configuration values
     *
     * @param string $id The configuration ID
     */
    public function getConfig($id)
    {
        try {
            $response = $this->request('GET', "$this->host/configs/view/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Gets a configuration values by ID
     *
     * @param string $id The configuration ID
     */
    public function getConfigById($id)
    {
        try {
            $response = $this->request('GET', "$this->host/configuration/view/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Gets a general configuration values by ID
     *
     * @param string $id The configuration ID
     */
    public function getGeneralConfigById($id)
    {
        try {
            $response = $this->request('GET', "$this->host/configuration/general/view/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
