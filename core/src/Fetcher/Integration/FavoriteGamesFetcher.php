<?php

namespace App\Fetcher\Integration;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class FavoriteGamesFetcher extends AbstractIntegration
{
    private $host;

    /**
     * Player Session
     *
     */
    private $playerSession;

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

        $this->host = $host;
        $this->playerSession = $playerSession;
    }

    /**
     * Get a list of favorite games
     *
     * @return array
     */
    public function getFavorites()
    {
        $cookieJar = $this->getCookieJar();

        if (!empty($this->playerSession->getUsername())) {
            $query['query'] = [
                'username' => $this->playerSession->getUsername()
            ];
        } else {
            $query = [
                'cookies' => $cookieJar
            ];
        }

        try {
            $response = $this->request('GET', "$this->host/games/favorites", $query);
        } catch (GuzzleException $e) {
            $this->logger->error('FavoriteGamesFetcher', [
                'component' => static::class,
                'source' => "$this->host/games/favorites",
                'username' => $query['query']['username'] ?? '',
                'response' => $e->getCode(),
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);
        if (!empty($data['body'])) {
            return array_column($data['body'], 'timestamp', 'id');
        }

        return $data['body'];
    }

    /**
     * Save a list of games as favorites
     *
     * @param array $data Data format ['GP1', 'GP2', 'GP3']
     *
     * @return array
     */
    public function saveFavorites(array $data, $timestamp = null)
    {
        $cookieJar = $this->getCookieJar();

        if (!empty($this->playerSession->getUsername())) {
            $query['query'] = [
                'username' => $this->playerSession->getUsername()
            ];
        } else {
            $query = [
                'cookies' => $cookieJar
            ];
        }

        try {
            $array = [];

            foreach ($data as $value) {
                $array[$value] = [
                    'id' => $value,
                    'timestamp' => $timestamp ?? time(),
                ];
            }

            $query['json'] = $array;

            $response = $this->request('PUT', "$this->host/games/favorites", $query);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }

    /**
     * Delete a list of favorite games
     *
     * @param array $data Data format ['GP1', 'GP2', 'GP3']
     *
     * @return array
     */
    public function removeFavorites(array $data)
    {
        $cookieJar = $this->getCookieJar();

        if (!empty($this->playerSession->getUsername())) {
            $query['query'] = [
                'username' => $this->playerSession->getUsername()
            ];
        } else {
            $query = [
                'cookies' => $cookieJar
            ];
        }

        $query['json'] = $data;

        try {
            $response = $this->request('DELETE', "$this->host/games/favorites", $query);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }
}
