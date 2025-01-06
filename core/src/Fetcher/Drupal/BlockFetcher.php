<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class BlockFetcher extends AbstractFetcher
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
     * Gets a custom block values by ID
     *
     * @param string $id The custom block ID
     */
    public function getBlockById($id)
    {
        try {
            $response = $this->request('GET', "$this->host/custom_block/view/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
