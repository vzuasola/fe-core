<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * Fetcher for GameProviders
 *
 * @todo All methods can consume the get all details, so it won't issue multiple
 * HTTP requests
 */
class GameProviderFetcher extends AbstractIntegration
{

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     * @param object $logger
     */
    public function __construct($session, $playerSession, Client $client, $host, $logger, $product)
    {
        parent::__construct($session, $playerSession, $client, $host, $logger, $product);

        $this->client = $client;
        $this->host = $host;
    }

    /**
     * Get details of provider from config.
     */
    public function getDetails($id)
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($id) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['details'][$id] = $data['body'];
                return $provider['details'][$id];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/$id", $options, $callback);
    }

    /**
     * Get Lobby details from config.
     */
    public function getLobby($id)
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($id) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['lobby'][$id] = $data['body'];
                return $provider['lobby'][$id];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/$id/lobby", $options, $callback);
    }

    /**
     *
     */
    public function getGameUrlById($provider, $id, $query = [])
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($provider) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['gameurl'][$provider] = $data['body'];
                return $provider['gameurl'][$provider];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->createRequest($this->client, 'GET', "$this->host/$provider/gameurl/$id", $options, $callback);
    }

    /**
     * Get all currencies from config.
     */
    public function getCurrencies($id)
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($id) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['currencies'][$id] = $data['body'];
                return $provider['currencies'][$id];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/$id/currencies", $options, $callback);
    }

    /**
     * Get all languages from config.
     */
    public function getLanguages($id)
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($id) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['languages'][$id] = $data['body'];
                return $provider['languages'][$id];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/$id/languages", $options, $callback);
    }

    /**
     * Get all assets from config.
     */
    public function getJavascriptAssets($id)
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($id) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['javascripts'][$id] = $data['body'];
                return $provider['javascripts'][$id];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/$id/javascripts", $options, $callback);
    }

    /**
     * Get all options from config.
     */
    public function getOptions($id)
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($id) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['options'][$id] = $data['body'];
                return $provider['options'][$id];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/$id/options", $options, $callback);
    }

    /**
     *
     */
    public function getToken($provider, $query = [])
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) use ($provider) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                $provider['gameurl'][$provider] = $data['body'];
                return $provider['gameurl'][$provider];
            }
            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->createRequest($this->client, 'GET', "$this->host/$provider/token", $options, $callback);
    }
}
