<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class ProductFetcher extends AbstractFetcher
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
     * Gets the list of products
     *
     * @param array $query The array with query Pparameters.
     */
    public function getProducts($query = [])
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        $options = [
            'query' => $query,
        ];

        $host = "$this->host/product_tabs/view";

        return $this->createRequest($this->client, 'GET', $host, $options, $callback, true);
    }
}
