<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class NodeFetcher extends AbstractFetcher
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
     */
    public function __construct(Client $client, $host, $logger, $product, $cacher)
    {
        parent::__construct($client, $logger, $product, $cacher);

        $this->host = $host;
    }

    /**
     * Desc
     *
     * @param string $id
     */
    public function getNodeById($id)
    {
        try {
            $response = $this->request('GET', "$this->host/node/view/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);
        return $data['body'];
    }

    /**
     * Desc
     *
     * @param string $alias The node path alias to fetch
     */
    public function getNodeByAlias($alias)
    {
        try {
            $response = $this->request('GET', "$this->host/node/alias", [
                'query' => [
                    'alias' => $alias
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
