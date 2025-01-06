<?php

namespace App\Async\Processor;

use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Promise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Exception\GuzzleException;

use App\Async\Definition;
use App\Async\ArrayDefinition;
use App\Async\CacheDefinition;
use App\Async\DefinitionCollection;
use App\Async\Utils\ArrayStructure;

/**
 * This class is based on the Promises/A+ resolution of Guzzle promises
 * which is complicated. That is why this class is also complicated.
 */
trait PromiseInspectionTrait
{
    /**
     * Allows you to asynchronously settle promises with a definition object
     *
     * @param array A collection of promises
     * @param DefinitionCollection|Definition A definition object
     *
     * @return array
     */
    public function settlePromisesWithDefinition($promises, $definitions)
    {
        $flatPromises = ArrayStructure::flatten($promises);
        $flat = Promise\settle($flatPromises)->wait();

        return $this->settleFlatPromises($definitions, $promises, $flat);
    }

    /**
     *
     */
    private function settleFlatPromises($definitions, $promises, $flat, $prefix = '')
    {
        $result = [];

        foreach ($promises as $key => $response) {
            if ($definitions instanceof DefinitionCollection) {
                $definition = $definitions->getDefinition($key);
            } else {
                $definition = $definitions[$key];
            }

            if (is_array($response)) {
                $callbacks = $definition->getCallbacks();
                $result[$key] = $this->settleFlatPromises($definition, $response, $flat, $prefix . $key . '.');

                if (!empty($callbacks)) {
                    foreach ($callbacks as $callback) {
                        $options = $definition->getOptions();
                        $result[$key] = $callback($result[$key], $options, $response);
                    }
                }
            } else {
                // check if the single promise instance is a fullfilled promise
                if ($response instanceof FulfilledPromise) {
                    $value = $flat[$prefix . $key]['value'] ?? [];
                    $options = method_exists($definition, 'getOptions') ? $definition->getOptions():[];
                    $callback = method_exists($definition, 'getCallbacks') ? $definition->getCallbacks():[];
                    $definition = new ArrayDefinition($value, $options, $callback);
                }

                $result[$key] = $this->inspectPromise(
                    $definition,
                    $flat[$prefix . $key],
                    $definition->getOptions()
                );
            }
        }

        return $result;
    }

    /**
     *
     */
    private function inspectPromise($definition, $promise, $options = [])
    {
        $result = null;
        $response = $this->getResponseFromPromise($promise);

        if ($response instanceof GuzzleException) {
            $callbacks = $definition->getCallbacks();
        } elseif ($response instanceof ResponseInterface) {
            $content = $response->getBody()->getContents();
            $callbacks = $definition->getCallbacks();
            $result = $content;

            $response->getBody()->rewind();

            $this->setCacheByDefinition($definition, $response, $content);
        } elseif ($response instanceof ArrayDefinition) {
            // Not sure what is this exactly, but this is not called at all.
            $result = $response->resolve();
        } elseif (is_string($response) && $json = json_decode($response, true)) {
            // This will check if the response is already a json response
            // which is usually a key collision of non-async fetchers
            // This will process the json response and convert it to array ready for use
            $result = $json['body'] ?? [];
        } else {
            // check if the single promise instance is a fullfilled promise
            // just dump out the response as the result
            $result = $response;
        }

        if ($result && !empty($callbacks)) {
            foreach ($callbacks as $callback) {
                $result = $callback($result, $options, $response);
            }
        }

        return $result;
    }

    /**
     * Assign response object based on response
     *
     * @param array The promise result
     *
     * @return object
     */
    private function getResponseFromPromise($result)
    {
        $response = [];

        if ($result['state'] == 'fulfilled') {
            $response = $result['value'];
        } elseif ($result['state'] == 'rejected') {
            $response = $result['reason'];
        }

        return $response;
    }
}
