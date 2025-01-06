<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use App\Fetcher\Integration\Exception\ClickatellException;
use GuzzleHttp\Client;

/**
 * SMS fetcher class
 */
class SmsFetcher extends AbstractIntegration
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
     * SMS
     * Player initiates the sms
     *
     * @param $smsData
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function sendSms($smsData)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/send/sms/", [
                'cookies' => $cookieJar,
                'json' => [
                    'smsdata' => $smsData
                ]
            ]);

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            if ($data['responseCode'] === 'INT040') {
                throw new ClickatellException($data['responseMessage']);
            }
        } catch (GuzzleException $e) {
            throw $e;
        }
    }
}
