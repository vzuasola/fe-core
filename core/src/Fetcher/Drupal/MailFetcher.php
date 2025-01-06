<?php

namespace App\Fetcher\Drupal;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Fetcher\AbstractFetcher;

/**
 *
 */
class MailFetcher extends AbstractFetcher
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * Script manager object
     *
     * @var object
     */
    private $scripts;

    /**
     * List of form configurations
     *
     * @var array
     */
    private $configurations;

    /**
     * Propery caching
     *
     * @var array
     */
    private $cache;

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
     * Submits a post email request to the data layer form
     *
     * @param array  $data The data to be submitted
     */
    public function sendMail($data)
    {
        $post = $data;

        try {
            $response = $this->request('POST', "$this->host/email/submission", [
                'json' => $post
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        return $response;
    }
}
