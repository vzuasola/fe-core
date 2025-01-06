<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class PreferencesFetcher extends AbstractIntegration
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
     * Get a list of preferences
     *
     * @return array
     */
    public function getPreferences($param = [])
    {
        if (isset($param['username'])) {
            $options = [
                'query' => [
                    'username' => $param['username']
                ]
            ];
        } else {
            $options = [
                'cookies' => $this->getCookieJar()
            ];
        }
        try {
            $response = $this->request('GET', "$this->host/preference", $options);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
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
        $data = json_decode($data, true);

        return $data['body'];
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
        $data = json_decode($data, true);

        return $data['body'];
    }
}
