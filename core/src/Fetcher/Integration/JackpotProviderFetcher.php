<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * Fetcher for Jackpot
 *
 * HTTP requests
 */
class JackpotProviderFetcher extends AbstractIntegration
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
     * Fetches jackpots by currency
     *
     * @param string $provider
     * @param string $currency
     */
    public function getJackpots($provider, $currency)
    {
        try {
            $response = $this->request('GET', "$this->host/$provider/$currency/");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Fetches the current total of a specific currency
     *
     * @param string $provider
     * @param string $currency
     */
    public function getTotal($provider, $currency)
    {
        try {
            $response = $this->request('GET', "$this->host/$provider/$currency/total");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Fetches jackpots by specifying a date
     *
     * @param string $provider
     * @param string $currency
     * @param string $date Date format should be yyyy-dd-mm
     *
     * $this->jackpotProvider->getJackpotsByDate('casino_jamboree', 'usd', '2018-08-23')
     */
    public function getJackpotsByDate($provider, $currency, $date)
    {
        try {
            $response = $this->request('GET', "$this->host/$provider/$currency/$date");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Fetches total jackpots by specifying a date
     *
     * @param string $provider
     * @param string $currency
     * @param string $date Date format should be yyyy-dd-mm
     *
     * $this->jackpotProvider->getJackpotsByDate('casino_jamboree', 'usd', '2018-08-23')
     */
    public function getTotalByDate($provider, $currency, $date)
    {
        try {
            $response = $this->request('GET', "$this->host/$provider/$currency/$date/total");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Fetches jackpots by specifying a date range
     *
     * @param string $provider
     * @param string $currency
     * @param string $from Date format should be yyyy-dd-mm
     * @param string $to Date format should be yyyy-dd-mm
     *
     * $this->jackpotProvider->getJackpotsByDateRange('casino_jamboree', 'usd', '2018-08-21', '2018-08-23')
     */
    public function getJackpotsByDateRange($provider, $currency, $from, $to)
    {
        try {
            $response = $this->request('GET', "$this->host/$provider/$currency/range/$from/$to");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Fetches total jackpots by specifying a date range
     *
     * @param string $provider
     * @param string $currency
     * @param string $from Date format should be yyyy-dd-mm
     * @param string $to Date format should be yyyy-dd-mm
     *
     * $this->jackpotProvider->getJackpotsByDateRange('casino_jamboree', 'usd', '2018-08-21', '2018-08-23')
     */
    public function getTotalByDateRange($provider, $currency, $from, $to)
    {
        try {
            $response = $this->request('GET', "$this->host/$provider/$currency/range/$from/$to/total");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Fetches total jackpots by specifying a date range
     *
     * @param string $provider
     * @param string $currency
     * @param string $game
     *
     * $this->jackpotProvider->getHitsByGame('casino_jamboree', 'usd', 'adv')
     */
    public function getHitsByGame($provider, $currency, $game)
    {
        try {
            $response = $this->request('GET', "$this->host/$provider/$currency/hits/$game");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
