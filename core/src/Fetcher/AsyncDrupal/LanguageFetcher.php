<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class LanguageFetcher extends AbstractFetcher
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
     * Gets a list of available languages
     *
     * @param array
     */
    public function getLanguages()
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        return $this->createRequest($this->client, 'GET', "$this->host/language/view", [], $callback, true);
    }
}
