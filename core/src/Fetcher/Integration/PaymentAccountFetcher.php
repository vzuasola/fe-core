<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class PaymentAccountFetcher extends AbstractIntegration
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
     * Check whether the user has a payment account
     */
    public function hasAccount($product = null, $username = null)
    {
        $cookieJar = $this->getCookieJar();

        try {
            if ($product) {
                $prefix = $product;

                if ($username) {
                    $prefix = "$prefix/$username";
                }

                $response = $this->request('GET', "$this->host/paymentaccount/$prefix", [
                    'cookies' => $cookieJar
                ]);
            } else {
                $response = $this->request('GET', "$this->host/paymentaccount/", [
                    'cookies' => $cookieJar
                ]);
            }
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
