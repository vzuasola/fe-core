<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * Fetcher for Jackpot
 *
 * HTTP requests
 */
class JackpotFetcher extends AbstractIntegration
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * API path to be used
     *
     * @var string
     */
    private $path;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     * @param object $logger
     */
    public function __construct($session, Client $client, $host, $logger, $product)
    {
        parent::__construct($session, $client, $host, $logger, $product);
        $this->host = $host;
    }

    /**
     * Get the most recent fetched jackpot data
     *
     * @param  string $provider
     * @param  string $currency
     * @return array
     */
    public function getCurrentJackpots($provider, $currency)
    {
        $currency = strtolower($currency);
        $jackpots = $this->fetchJackpotData("$this->host/$provider/$currency/");
        return $jackpots['jackpot'];
    }

    /**
     * Get the most recent fetched total jackpot
     *
     * @param  string $provider
     * @param  string $currency
     * @return array
     */
    public function getCurrentTotalJackpot($provider, $currency)
    {
        $currency = strtolower($currency);
        $jackpots = $this->fetchJackpotData("$this->host/$provider/$currency/total/");
        return $jackpots['jackpot'];
    }

    /**
     * Get the most recent fetched jackpot per game
     *
     * @param  string $provider
     * @param  string $currency
     * @param  string $gameId
     * @return array
     */
    public function getCurrentJackpotByGame($provider, $currency, $gameId)
    {
        $currency = strtolower($currency);
        $jackpots = $this->fetchJackpotData("$this->host/$provider/$currency/bygame/$gameId/");
        return $jackpots['jackpot'];
    }

    /**
     * Fetch the jackpot data from API
     *
     * @param  string $path
     * @param  string $query
     * @return array
     */
    private function fetchJackpotData($path, $query = [])
    {
        try {
            $params = [];
            // If query string is available
            if (!empty($query)) {
                $params['query'] = $query;
            }
            $response = $this->request('GET', $path, $params);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
