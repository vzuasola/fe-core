<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class PaymentFetcher extends AbstractIntegration
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
     * Get payment methods from cashier
     */
    public function getPaymentMethods($transactionId)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('GET', "$this->host/payment/payment-methods/$transactionId", [
                'cookies' => $cookieJar,
                'header' => [

                ],
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
