<?php

namespace App\Async;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use GuzzleHttp\Promise\FulfilledPromise as ArrayPromise;

class AsyncResolver
{
    /**
     *
     */
    public function resolve($definitions, &$promises = [])
    {
        foreach ($definitions as $definition) {
            if ($definition instanceof DefinitionCollection) {
                $this->resolve($definition->getDefinitions(), $promises);
                continue;
            }

            if ($definition instanceof Definition) {
                $promises[] = $this->getPromise($definition);
                continue;
            }

            $promise = new ArrayPromise($definition);
            $promise->definition = $definition;

            $promises[] = $promise;
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

        $request = $client->requestAsync(
            $method,
            $uri,
            $definition->getOptions()
        );

        \App\Kernel::profiler()->setMessage($uri, 'Async Network');

        $request->definition = $definition;

        return $request;
    }
}
