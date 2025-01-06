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
class PlayerGameFetcher extends AbstractIntegration
{
    /**
     * The host name to connect to
     *
     * @var string
     */

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
        $this->playerSession = $playerSession;
        $this->host = $host;
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
