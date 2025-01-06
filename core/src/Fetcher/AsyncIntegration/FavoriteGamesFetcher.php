<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class FavoriteGamesFetcher extends AbstractIntegration
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
     * Get a list of favorite games
     *
     * @return array
     */
    public function getFavorites()
    {
        $cookieJar = $this->getCookieJar();
        $callback = function ($data, $options, $response) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);

                if (!empty($data['body'])) {
                    return array_column($data['body'], 'timestamp', 'id');
                }
            }

            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/games/favorites", $options, $callback);
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

        try {
            $array = [];

            foreach ($data as $value) {
                $array[$value] = [
                    'id' => $value,
                    'timestamp' => $timestamp ?? time(),
                ];
            }

            $response = $this->request('PUT', "$this->host/games/favorites", [
                'cookies' => $cookieJar,
                'json' => $array,
            ]);
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

        try {
            $response = $this->request('DELETE', "$this->host/games/favorites", [
                'cookies' => $cookieJar,
                'json' => $data,
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }
}
