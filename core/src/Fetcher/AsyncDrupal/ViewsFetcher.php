<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class ViewsFetcher extends AbstractFetcher
{
    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product)
    {
        parent::__construct($client, $logger, $product);

        $this->client = $client;
        $this->host = $host;
        $this->logger = $logger;
        $this->product = $product;
    }

    /**
     * Get a Drupal views via its ID
     *
     * @param string $id The view id to fetch.
     * @param array  $context The array with contexual filter parameters.
     */
    public function getViewById($id, $context = null)
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        $options = [
            'query' => $context
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/views/$id", $options, $callback, true);
    }
}
