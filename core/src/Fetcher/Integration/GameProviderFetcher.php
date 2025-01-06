<?php

namespace App\Fetcher\Integration;

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
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * The player Sesion Object
     *
     * @var object
     */
    private $playerSession;

    /**
     * API path to be used
     *
     * @var string
     */
    private $path;

    /**
     * Cache object
     *
     * @var array
     */
    private $provider;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     * @param object $logger
     */
    public function __construct($session, $playerSession, Client $client, $host, $logger, $product)
    {
        parent::__construct($session, $client, $host, $logger, $product);
        $this->playerSession = $playerSession;
        $this->host = $host;
    }

    /**
     *
     */
    public function getDetails($id, $query = [])
    {
        if (!isset($this->provider['details'][$id])) {
            $cookieJar = $this->getCookieJar();

            $options = [
                'cookies' => $cookieJar
            ];

            if (!empty($query)) {
                $options['query'] = $query;
            }

            try {
                $response = $this->request('GET', "$this->host/$id", $options);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->provider['details'][$id] = $data['body'];
        }

        return $this->provider['details'][$id];
    }

    /**
     *
     */
    public function getLobby($id, $query = [])
    {
        if (!isset($this->provider['lobby'][$id])) {
            if (!empty($query)) {
                $options['query'] = $query;

            }

            $secureToken = $this->playerSession->getSecureToken();
            $playerId = $this->playerSession->getPlayerId();
            if (!empty($secureToken) && !empty($playerId)) {
                $options['query']['options']['secureToken'] = $secureToken;
                $options['query']['options']['playerId'] = $playerId;
            } else {
                $cookieJar = $this->getCookieJar();
                $options['cookies'] = $cookieJar;
            }

            try {
                $response = $this->request('GET', "$this->host/$id/lobby", $options);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->provider['lobby'][$id] = $data['body'];
        }

        return $this->provider['lobby'][$id];
    }

    /**
     *
     */
    public function getGameUrlById($provider, $id, $query = [])
    {
        if (!isset($this->provider['gameurl'][$id])) {
            if (!empty($query)) {
                $options['query'] = $query;

            }

            $secureToken = $this->playerSession->getSecureToken();
            $playerId = $this->playerSession->getPlayerId();
            if (!empty($secureToken) && !empty($playerId)) {
                $options['query']['options']['secureToken'] = $secureToken;
                $options['query']['options']['playerId'] = $playerId;
            } else {
                $cookieJar = $this->getCookieJar();
                $options['cookies'] = $cookieJar;
            }

            try {
                $response = $this->request('GET', "$this->host/$provider/gameurl/$id", $options);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->provider['gameurl'][$provider] = $data['body'];
        }

        return $this->provider['gameurl'][$provider];
    }

    /**
     *
     */
    public function getCurrencies($id, $query = [])
    {
        if (!isset($this->provider['currencies'][$id])) {
            $cookieJar = $this->getCookieJar();

            $options = [
                'cookies' => $cookieJar
            ];

            if (!empty($query)) {
                $options['query'] = $query;
            }

            try {
                $response = $this->request('GET', "$this->host/$id/currencies", $options);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->provider['currencies'][$id] = $data['body'];
        }

        return $this->provider['currencies'][$id];
    }

    /**
     *
     */
    public function getLanguages($id, $query = [])
    {
        if (!isset($this->provider['languages'][$id])) {
            $cookieJar = $this->getCookieJar();

            $options = [
                'cookies' => $cookieJar
            ];

            if (!empty($query)) {
                $options['query'] = $query;
            }

            try {
                $response = $this->request('GET', "$this->host/$id/languages", $options);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->provider['languages'][$id] = $data['body'];
        }

        return $this->provider['languages'][$id];
    }

    /**
     *
     */
    public function getJavascriptAssets($id, $query = [])
    {
        if (!isset($this->provider['javascripts'][$id])) {
            $cookieJar = $this->getCookieJar();

            $options = [
                'cookies' => $cookieJar
            ];

            if (!empty($query)) {
                $options['query'] = $query;
            }

            try {
                $response = $this->request('GET', "$this->host/$id/javascripts", $options);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->provider['javascripts'][$id] = $data['body'];
        }

        return $this->provider['javascripts'][$id];
    }

    /**
     *
     */
    public function getOptions($id)
    {
        if (!isset($this->provider['options'][$id])) {
            $cookieJar = $this->getCookieJar();

            try {
                $response = $this->request('GET', "$this->host/$id/options", [
                    'cookies' => $cookieJar
                ]);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->provider['options'][$id] = $data['body'];
        }

        return $this->provider['options'][$id];
    }

    /**
     *
     */
    public function getToken($provider, $query = [])
    {
        $cookieJar = $this->getCookieJar();

        $options = [
            'cookies' => $cookieJar
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->request('GET', "$this->host/$provider/token", $options);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        $this->provider['gameurl'][$provider] = $data['body'];

        return $this->provider['gameurl'][$provider];
    }

    /**
     * Get launch url via PlayerGame API using external gameID
     */
    public function getGameUrlByExtGameId($portalName, $extGameId, $query = [])
    {
        $cookieJar = $this->getCookieJar();

        $options = [
            'cookies' => $cookieJar
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->request('GET', "$this->host/playergame/$portalName/$extGameId", $options);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data;
    }
}
