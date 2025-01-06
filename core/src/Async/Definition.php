<?php

namespace App\Async;

use GuzzleHttp\Client;

use App\Async\Processor\PromiseResolutionTrait;
use App\Async\Processor\PromiseInspectionTrait;
use App\Async\Processor\PromiseResponseCacheTrait;

/**
 * An async definition object
 *
 * A definition is a resource that is deferred, data will be fetch after
 * processing it
 */
class Definition
{
    use PromiseResolutionTrait;
    use PromiseInspectionTrait;
    use PromiseResponseCacheTrait;

    /**
     *
     */
    private $callback = [];

    /**
     *
     */
    private $options = [];

    /**
     * @var array An array of headers that should be sent to cms-api but should not be used in the cache key.
     */
    private $extraHeaders = [];

    /**
     * Public constructor
     */
    public function __construct(Client $client, $method, $uri, $options, $callback = null, $isCacheable = false)
    {
        $this->client = $client;
        $this->method = $method;
        $this->uri = $uri;
        $this->options = $options;
        $this->callback[] = $callback;
        $this->isCacheable = $isCacheable;
    }

    /**
     *
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     *
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     *
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     */
    public function getCallbacks()
    {
        return $this->callback;
    }

    /**
     *
     */
    public function withCallback($callback)
    {
        $this->callback[] = $callback;

        return $this;
    }

    /**
     *
     */
    public function isCacheable()
    {
        return $this->isCacheable;
    }

    /**
     *
     */
    public function resolve()
    {
        $definitions = ['definition' => $this];

        $promises = $this->getPromisesByDefinitions($definitions);
        $result = $this->settlePromisesWithDefinition($promises, $definitions);

        return reset($result);
    }

    public function getExtraHeaders()
    {
        return $this->extraHeaders;
    }

    public function setExtraHeaders(array $headers)
    {
        if (!empty($headers)) {
            $this->extraHeaders = $headers;
        }
    }
}
