<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class PreferencesFetcher extends AbstractIntegration
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
     * Get a list of preferences
     *
     * @return array
     */
    public function getPreferences()
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

        return $this->createRequest($this->client, 'GET', "$this->host/preference", $options, $callback);
    }

    /**
     * Save a list of data as preference
     *
     * @param string $key
     *
     * @return array
     */
    public function savePreference($key, $value)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('PUT', "$this->host/preference", [
                'cookies' => $cookieJar,
                'json' => [$key => $value],
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }

    /**
     * Delete a list of recent games
     *
     * @return array
     */
    public function removePreference(...$keys)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('DELETE', "$this->host/preference", [
                'cookies' => $cookieJar,
                'json' => $keys,
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }
}
