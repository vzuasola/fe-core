<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class FileFetcher extends AbstractFetcher
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
     *
     */
    public function getFileById($id)
    {
        $file = $this->getFileInfoById($id);

        return $file[0];
    }

    /**
     *
     */
    public function getFileInfoById($id)
    {
        try {
            $response = $this->request('GET', "$this->host/files/view/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
