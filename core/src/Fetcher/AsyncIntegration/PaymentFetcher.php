<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class PaymentFetcher extends AbstractIntegration
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
     * Check whether the user has a payment account
     */
    public function getPaymentMethods($transactionId)
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

        return $this->createRequest(
            $this->client,
            'GET',
            "$this->host/payment/payment-methods/$transactionId",
            $options,
            $callback
        );
    }
}
