<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * Receive News fetcher class
 */
class ReceiveNewsFetcher extends AbstractIntegration
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
     * @param object $logger
     */
    public function __construct($session, Client $client, $host, $logger, $product)
    {
        parent::__construct($session, $client, $host, $logger, $product);

        $this->host = $host;
    }

    /**
     * Get receive news
     *
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function isSubscribed()
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('GET', "$this->host/user/receive/news/", [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Update receive news
     *
     * @param $newPassword
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function setSubscription($receiveNews)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/update/receive/news/", [
                'cookies' => $cookieJar,
                'json' => [
                    'receiveNews' => $receiveNews,
                ]
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
