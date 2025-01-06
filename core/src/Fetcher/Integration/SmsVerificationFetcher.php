<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Fetcher\Integration\Exception\ServerDownException;

/**
 * SMS Verification fetcher class
 */
class SmsVerificationFetcher extends AbstractIntegration
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
     * SMS Verification
     * Player initiates the sms verificationrequest
     *
     * @param $subTypeId
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function sendSmsVerificationCode($subTypeId)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/send/verification/code/", [
                'cookies' => $cookieJar,
                'json' => [
                    'subTypeId' => $subTypeId
                ]
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            if (isset($response->getHeaders()['Is-Mid']) && $response->getHeaders()['Is-Mid'][0] === "off") {
                throw new ServerDownException('MID is Down');
            }
            throw $e;
        }

        if (isset($response->getHeaders()['Is-Mid']) && $response->getHeaders()['Is-Mid'][0] === "off") {
            throw new ServerDownException('MID is Down');
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['responseCode'];
    }

    /**
     * SMS Verification
     * Player verify sms code
     *
     * @param $data
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function submitSmsVerificationCode($data)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/submit/verification/code/", [
                'cookies' => $cookieJar,
                'json' => [
                    'data' => $data
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
     * Check SMS Verification Status
     *
     * @param $subTypeId
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function checkSmsStatus($subTypeId)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/check/sms/status/", [
                'cookies' => $cookieJar,
                'json' => [
                    'subTypeId' => $subTypeId
                ]
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }
        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['responseCode'];
    }
}
