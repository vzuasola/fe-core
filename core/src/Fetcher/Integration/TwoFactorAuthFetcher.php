<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Fetcher\Integration\Exception\ServerDownException;
use App\Fetcher\Integration\Exception\TokenQuotaExceededException;
use App\Fetcher\Integration\Exception\InvalidTokenTypeException;

/**
 * Two Factor Authentication fetcher class
 */
class TwoFactorAuthFetcher extends AbstractIntegration
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
     * Verify OTP Code
     * Player verifies OTP code
     *
     * @param $code
     * @return array
     *
     * @throws GuzzleException
     */
    public function validateOTPCode($code)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/validate/otp/", [
                'cookies' => $cookieJar,
                'json' => [
                    'code' => $code
                ]
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data;
    }

    /**
     * Generate new OTP code
     * Player generates new OTP code
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function generateOTPCode()
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/generate/otp/", [
                'cookies' => $cookieJar,
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data;
    }
}
