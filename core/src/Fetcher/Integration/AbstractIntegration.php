<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Client;
use App\Utils\Host;
use App\Fetcher\AbstractFetcher;

/**
 *
 */
abstract class AbstractIntegration extends AbstractFetcher
{
    /**
     * The Guzzle client
     *
     * @var Client
     */
    private $client;

    /**
     * The session handler object
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * The host name
     *
     * @var string
     */
    private $host;

    /**
     * The cookie domain host
     *
     * @var string
     */
    private $cookie;

    /**
     * Public constructor.
     */
    public function __construct($session, Client $client, $host, $logger, $product)
    {
        parent::__construct($client, $logger, $product);

        $this->client = $client;
        $this->session = $session;
        $this->host = $host;
    }

    /**
     * Gets the cookie JAR from the current session
     *
     * @return CookieJarInterface
     */
    protected function getCookieJar()
    {
        $cookie = Host::getDomainFromUri($this->host);

        return CookieJar::fromArray([
            'PHPSESSID' => $this->session->get('token')
        ], ".$cookie");
    }
}
