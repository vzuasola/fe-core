<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class ViewsFetcher extends AbstractFetcher
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
     * Get a Drupal views via its ID
     *
     * @param string $id The view id to fetch.
     * @param array $context The array with contexual Filter Parameters.
     */
    public function getViewById($id, $context = null)
    {
        try {
            $options = $this->getDefaultRequestOptions([
                'query' => $context,
            ]);

            $response = $this->request('GET', "$this->host/views/$id", $options);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
