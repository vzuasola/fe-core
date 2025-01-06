<?php

namespace App\Fetcher\AsyncIntegration;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;

use App\Utils\Host;
use App\Fetcher\AbstractFetcher;

/**
 * Abstract class for all integration based fetchers
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
     * The player session object
     *
     * @var object
     */
    private $playerSession;

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
    public function __construct($session, $playerSession, Client $client, $host, $logger, $product)
    {
        parent::__construct($client, $logger, $product);

        $this->session = $session;
        $this->playerSession = $playerSession;
        $this->client = $client;
        $this->host = $host;
    }

    /**
     * Gets the cookie JAR from the current session
     *
     * @return CookieJarInterface
     */
    protected function getCookieJar()
    {
        if ($this->playerSession->isLogin()) {
            $cookie = Host::getDomainFromUri($this->host);

            return CookieJar::fromArray([
                'PHPSESSID' => $this->session->get('token')
            ], ".$cookie");
        }
    }
}
