<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class LanguageFetcher extends AbstractFetcher
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * Private property for static response caching
     *
     * @var array
     */
    private $languages;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product, $cacher)
    {
        parent::__construct($client, $logger, $product, $cacher);
        $this->product = $product;
        $this->host = $host;
    }

    /**
     * Gets a list of available languages
     *
     * @param array
     */
    public function getLanguages()
    {
        if (!isset($this->languages[$this->product])) {
            try {
                $response = $this->request('GET', "$this->host/language/view");
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->languages[$this->product] = $data['body'];
        }

        return $this->languages[$this->product];
    }
}
