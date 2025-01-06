<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class DomainFetcher extends AbstractFetcher
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * @var ConfigFetcher $config
     */
    private $config;

    /**
     * Private property for static response caching
     *
     * @var array
     */
    private $placeholders;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product, $cacher, $config)
    {
        parent::__construct($client, $logger, $product, $cacher);

        $this->host = $host;
        $this->config = $config;
    }

    /**
     * Gets the domain placeholders
     *
     * @return array
     */
    public function getPlaceholders()
    {
        $domain = $_SERVER['HTTP_HOST'];

        if (!isset($this->placeholders[$domain])) {
            try {
                $version = $this->getVersion();
                $response = $this->request('GET', "$this->host/domain/view/$domain?$version");
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $this->placeholders[$domain] = $data['body'];
        }

        return $this->placeholders[$domain];
    }

    /**
     * Gets the domain placeholder value of a specific token
     *
     * @param string $token
     *
     * @return string
     */
    public function getPlaceholder($token)
    {
        $placeholders = $this->getPlaceholders();

        return $placeholders[$token] ?? null;
    }

    /**
     * Gets the domain placeholders
     *
     * @param string $domain The current domain to fetch
     *
     * @return array
     */
    public function getPlaceholderByDomain($domain)
    {
        if (!isset($this->placeholders[$domain])) {
            try {
                $version = $this->getVersion();
                $response = $this->request('GET', "$this->host/domain/view/$domain?$version");
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            return $data['body'];
        }

        return $this->placeholders[$domain];
    }

    /**
     * Get the domain version to be added on the request
     *
     * @return string
     * @throws GuzzleException
     */
    private function getVersion()
    {
        try {
            $toggleConfiguration = $this->config->getGeneralConfigById('toggle_configuration');
            $isV2Enabled = $toggleConfiguration['use_v2_domain'] ?? false;
            if ($isV2Enabled) {
                return "version=v2";
            }
            return "";
        } catch (\Throwable $e) {
            return "";
        }
    }
}
