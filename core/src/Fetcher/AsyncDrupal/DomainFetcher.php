<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class DomainFetcher extends AbstractFetcher
{
    /**
     * @var ConfigFetcher
     */
    private $config;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product, $config)
    {
        parent::__construct($client, $logger, $product);

        $this->client = $client;
        $this->host = $host;
        $this->logger = $logger;
        $this->product = $product;
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

        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };

        $version = $this->getVersion();
        $url = "$this->host/domain/view/$domain?$version";
        return $this->createRequest($this->client, 'GET', $url, [], $callback, true);
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
        $definition = $this->getPlaceholders();

        return $definition->withCallback(function ($data, $options) use ($token) {
            return $data[$token] ?? null;
        });
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
        $callback = function ($data, $options) {
            if (!empty($data)) {
                $data = json_decode($data, true);
                return $data['body'];
            }
        };
        $version = $this->getVersion();
        $url = "$this->host/domain/view/$domain?$version";
        return $this->createRequest($this->client, 'GET', $url, [], $callback, true);
    }

    /**
     * Get the domain version to be added on the request
     *
     * @return string
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
