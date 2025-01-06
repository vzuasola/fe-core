<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class NodeFetcher extends AbstractFetcher
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
     * Desc
     *
     * @param string $id
     */
    public function getNodeById($id)
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        return $this->createRequest($this->client, 'GET', "$this->host/node/view/$id", [], $callback, true);
    }

    /**
     * Desc
     *
     * @param string $alias The node path alias to fetch
     */
    public function getNodeByAlias($alias)
    {
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        $options = [
            'query' => [
                'alias' => $alias
            ],
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/node/alias", $options, $callback, true);
    }
}
