<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * Receive News fetcher class
 */
class ReceiveNewsFetcher extends AbstractIntegration
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
        $callback = function ($data, $options, $response) {
            if (!empty($data)) {
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                return $data['body'];
            }

            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

         return $this->createRequest($this->client, 'GET', "$this->host/user/receive/news/", $options, $callback);
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
