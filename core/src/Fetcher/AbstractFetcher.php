<?php

namespace App\Fetcher;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;

use Slim\Http\Stream;

use App\Async\Definition;
use App\Async\DefinitionCollection;
use App\Utils\IP;

/**
 *
 */
abstract class AbstractFetcher
{
    use LogTrait;
    use ConfigurableTrait;

    /**
     * The Guzzle client
     *
     * @var Client
     */
    private $client;

    /**
     * The monolog logger object
     *
     * @var object
     */
    protected $logger;

    /**
     * The log definition
     *
     * @var array
     */
    private $definition;

    /**
     * The product code
     *
     * @var string
     */
    protected $product;

    /**
     * The language code
     *
     * @var string
     */
    protected $language;

    /**
     * Fetcher cache object
     *
     * @var object
     */
    private $cacher;

    /**
     * Additional logging data
     *
     * @var array
     */
    private $data;

    /**
     * Public constructor.
     */
    public function __construct(
        Client $client,
        $logger,
        $product,
        Cache $cacher = null
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->product = $product;
        $this->cacher = $cacher;
    }

    /**
     *
     */
    protected function request($method, $uri, $options = [])
    {
        $product = $this->product;
        $key = "$method:$uri:$product";

        $headers = $this->client->getConfig('headers');

        // add language to the cache key prefix if it exists
        $lang = $this->language ?? $headers['Language'] ?? false;
        if ($lang) {
            $key = "$key:$lang";
        }

        $options['headers']['Product'] = $this->product;

        if (isset($this->language)) {
            $options['headers']['Language'] = $this->language;
        }

        $ipAddress = IP::getIpAddress() ?? "";

        // Forward customer IP address as a request header
        $extraHeaders = [
            'headers' => [
                'X-Custom-IP' => $ipAddress
            ]
        ];

        $this->data = [
            'country_code' => $_SERVER['HTTP_X_CUSTOM_LB_GEOIP_COUNTRY'] ?? "",
            'ip' => $ipAddress,
            'duration' => 0,
            'cached' => false,
            'username' => $this->getOptionsValue($options, 'username'),
        ];

        try {
            if ($this->cacher &&
                $item = $this->cacher->get($key, $options)
            ) {
                $this->data['duration'] = 0;
                $this->data['cached'] = true;
                $response = $item['response'];
                $stream = $this->createStreamFromArray($item['body']);

                $response = $response->withBody($stream);
            } else {
                \App\Kernel::profiler()->start(__METHOD__);

                $response = $this->client->request($method, $uri, array_merge_recursive($options, $extraHeaders));

                $time = \App\Kernel::profiler()->stop(__METHOD__);
                $uri = "$uri ($time ms)";
                $this->data['duration'] = $time;

                \App\Kernel::profiler()->setMessage($uri, 'Network', true);

                $body = $response->getBody()->getContents();
                $response->getBody()->rewind();

                \App\Kernel::profiler()->debug([
                    'fetcher' => static::class,
                    'request' => func_get_args(),
                    'response' => $body,
                ], 'Network');

                $body = $response->getBody()->getContents();
                $response->getBody()->rewind();

                if ($this->cacher) {
                    $this->cacher->set($key, ['response' => $response, 'body' => $body], $options);
                }
            }

            $body = $response->getBody()->getContents();
            $response->getBody()->rewind();

            $this->logInfo($uri, $body);
        } catch (ConnectException $e) {
            $this->logException('connect_failed', $uri, $e);

            throw $e;
        } catch (ServerException $e) {
            $this->logException('request_error', $uri, $e);

            throw $e;
        } catch (ClientException $e) {
            $this->logException('request_failed', $uri, $e);

            throw $e;
        } catch (Exception $e) {
            $this->logException('unhandled_exception', $uri, $e);

            throw $e;
        }

        return $response;
    }

    /**
     *
     */
    protected function createRequest(
        Client $client,
        $method,
        $uri,
        $options,
        $callback = null,
        $isCacheable = false
    ) {
        $options['headers']['Product'] = $this->product;

        if (isset($this->language)) {
            $options['headers']['Language'] = $this->language;
        }

        if (isset($this->assetPrefix)) {
            $options['headers']['X-FE-Base-Uri'] = $this->assetPrefix;
        }

        $requestDefinition = new Definition($client, $method, $uri, $options, $callback, $isCacheable);

        $ipAddress = IP::getIpAddress() ?? "";
        $extraHeaders = ['X-Custom-IP' => $ipAddress];
        $requestDefinition->setExtraHeaders($extraHeaders);

        return $requestDefinition;
    }

    private function createStreamFromArray($object)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $object);

        return new Stream($stream);
    }

    /**
     * Set the protected language
     * This will override the default language that is set ($container->get('lang'))
     *
     * @see App\Dependencies\Client\DrupalClient
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Retrieves the specified option value if present
     *
     */
    private function getOptionsValue($options, $searchKey)
    {
        foreach ($options as $key => $item) {
            if ($key === $searchKey) {
                return $item;
            } elseif (is_array($item) && $tierItem = $this->getOptionsValue($item, $searchKey)) {
                return $tierItem;
            }
        }

        return null;
    }
}
