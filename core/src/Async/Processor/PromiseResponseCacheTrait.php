<?php

namespace App\Async\Processor;

use App\Kernel;

/**
 * Class for caching promise responses
 */
trait PromiseResponseCacheTrait
{
    private function getCacher()
    {
        $container = Kernel::container();
        return $container->get('fetcher_cache');
    }

    /**
     * Get cache key base from definition details
     *
     * @param object $definition An async request definition
     *
     * @return string
     */
    public function getCacheKeyByDefinition($definition)
    {
        $method = $definition->getMethod();
        $uri = $definition->getUri();
        $options = $definition->getOptions();

        $key = "$method:$uri";

        $headers = $definition->getClient()->getConfig('headers');

        // add product as cache key
        $productKey = $options['headers']['Product'] ?? $headers['Product'] ?? false;
        if ($productKey) {
            $key = "$key:$productKey";
        }

        $language = $headers['Language'] ?? false;
        if ($language) {
            $key = "$key:$language";
        }

        return $key;
    }

    /**
     * Cache promise response by definition
     *
     * @param object $definition An async request definition
     * @param mixed $response A mixed collection of response
     * @param string $body JSON response
     *
     * @return void
     */
    public function setCacheByDefinition($definition, $response, $body)
    {
        if ($definition->isCacheable()) {
            $cacher = $this->getCacher();
            if ($cacher) {
                $cacher->set(
                    $this->getCacheKeyByDefinition($definition),
                    [
                        'response' => $response,
                        'body' => $body
                    ],
                    $definition->getOptions()
                );
            }
        }
    }

    /**
     * Convert definition to actual data
     *
     * @param object $definition An async request definition
     *
     * @return mixed
     */
    public function getCacheByDefinition($definition)
    {
        if ($definition->isCacheable()) {
            $cacher = $this->getCacher();
            if ($cacher &&
                    $item = $cacher->get(
                        $this->getCacheKeyByDefinition($definition),
                        $definition->getOptions()
                    )) {
                        return $item;
            }
        }

        return false;
    }
}
