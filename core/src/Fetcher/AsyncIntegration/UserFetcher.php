<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class UserFetcher extends AbstractIntegration
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
     * Desc
     */
    public function getPlayerDetails()
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

        return $this->createRequest($this->client, 'GET', "$this->host/user/", $options, $callback);
    }

    /**
     * Update Player Profile
     *
     * @param $playerDetails
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function setPlayerDetails($playerDetails)
    {
        $cookieJar = $this->getCookieJar();
        try {
            $response = $this->request('POST', "$this->host/user/update/player/profile/", [
                'cookies' => $cookieJar,
                'json' => [
                    'playerDetails' => $playerDetails,
                ]
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['responseCode'];
    }

    /**
     * Get Player Bonus History
     */
    public function getPlayerBonusHistory()
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

        return $this->createRequest($this->client, 'GET', "$this->host/user/bonus/history/", $options, $callback);
    }
}
