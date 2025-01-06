<?php

namespace App\Async\Processor;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use GuzzleHttp\Promise\FulfilledPromise;

trait PromiseResolutionTrait
{
    /**
     * Gets collection of promises from a DefinitionCollection/Defintion
     *
     * @param DefinitionCollection|Definition A definition object
     *
     * @return array
     */
    public function getPromisesByDefinitions($definitions)
    {
        $promises = [];

        foreach ($definitions as $key => $definition) {
            if ($definition instanceof DefinitionCollection) {
                $promises[$key] = $this->getPromiseCollection($definition);
                continue;
            }

            if ($definition instanceof Definition) {
                if ($item = $this->getCacheByDefinition($definition)) {
                    $promises[$key] = new FulfilledPromise($item['body']);
                } else {
                    $promises[$key] = $this->getPromise($definition);
                }
                continue;
            }

            $promises[$key] = new FulfilledPromise($definition);
        }

        return $promises;
    }

    /**
     *
     */
    private function getPromiseCollection($definition)
    {
        $promises = [];

        foreach ($definition->getDefinitions() as $key => $definition) {
            if ($definition instanceof DefinitionCollection) {
                $promises[$key] = $this->getPromiseCollection($definition);
                continue;
            }

            if ($definition instanceof Definition) {
                if ($item = $this->getCacheByDefinition($definition)) {
                    $promises[$key] = new FulfilledPromise($item['body']);
                } else {
                    $promises[$key] = $this->getPromise($definition);
                }
                continue;
            }

            $promises[$key] = new FulfilledPromise($definition);
        }

        return $promises;
    }

    /**
     *
     */
    private function getPromise($definition)
    {
        $client = $definition->getClient();
        $method = $definition->getMethod();
        $uri = $definition->getUri();

        $options = $definition->getOptions();
        $extraHeaders = $definition->getExtraHeaders();
        if (!empty($extraHeaders)) {
            $options = array_merge_recursive($options, ['headers' => $extraHeaders]);
        }

        $request = $client->requestAsync(
            $method,
            $uri,
            $options
        );

        \App\Kernel::profiler()->setMessage($uri, 'Async Network');

        return $request;
    }
}
