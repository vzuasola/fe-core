<?php

namespace App\Plugins\Middleware;

/**
 *
 */
class MiddlewareManager
{
    /**
     * Exposed the service container on the form manager
     */
    protected $container;

    /**
     * The system configurations manager
     */
    protected $configuration;

    /**
     * List of all available middlewares
     *
     * @var array
     */
    private $middlewareList;

    /**
     * Public constructor
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->configuration = $container->get('configuration_manager');
    }

    /**
     * Gets all list of defined sections
     */
    public function getMiddlewareList()
    {
        if (empty($this->middlewareList)) {
            $values = $this->configuration->getConfiguration('middleware');

            if (!empty($values['middlewares'])) {
                $this->middlewareList = $values['middlewares'];
            }
        }

        return $this->middlewareList;
    }

    /**
     *
     */
    public function getRequestMiddlewares()
    {
        $stack = [];
        $special = [];

        $middlewares = $this->getMiddlewareList();

        foreach ($middlewares['request'] as $key => $middleware) {
            if (is_array($middleware) && isset($middleware['class'])) {
                if (isset($middleware['execute_before']) || isset($middleware['execute_after'])) {
                    $special[$key] = $middleware;
                } else {
                    $stack[$key] = $middleware['class'];
                }
            } elseif ($middleware) {
                $stack[$key] = $middleware;
            }
        }

        foreach ($special as $key => $middleware) {
            if (isset($middleware['execute_before'])) {
                $stack = $this->insertBefore($middleware['execute_before'], $stack, $key, $middleware['class']);
            } elseif (isset($middleware['execute_after'])) {
                $stack = $this->insertAfter($middleware['execute_after'], $stack, $key, $middleware['class']);
            }
        }

        return $stack;
    }

    /**
     *
     */
    public function getResponseMiddlewares()
    {
        $stack = [];
        $special = [];

        $middlewares = $this->getMiddlewareList();

        foreach ($middlewares['response'] as $key => $middleware) {
            if (is_array($middleware) && isset($middleware['class'])) {
                if (isset($middleware['execute_before']) || isset($middleware['execute_after'])) {
                    $special[$key] = $middleware;
                } else {
                    $stack[$key] = $middleware['class'];
                }
            } else {
                $stack[$key] = $middleware;
            }
        }

        foreach ($special as $key => $middleware) {
            if (isset($middleware['execute_before'])) {
                $stack = $this->insertBefore($middleware['execute_before'], $stack, $key, $middleware['class']);
            } elseif (isset($middleware['execute_after'])) {
                $stack = $this->insertAfter($middleware['execute_after'], $stack, $key, $middleware['class']);
            }
        }

        return $stack;
    }

    /**
     *
     */
    public function getCacheMiddlewares()
    {
        $middlewares = $this->getMiddlewareList();

        return $middlewares['cache'] ?? [];
    }

    /*
     * Inserts a new key/value before the key in the array
     */
    private function insertBefore($position, $array, $key, $newValue)
    {
        if (array_key_exists($position, $array)) {
            $new = [];

            foreach ($array as $k => $value) {
                if ($k === $position) {
                    $new[$key] = $newValue;
                }

                $new[$k] = $value;
            }

            return $new;
        }
    }

    /*
     * Inserts a new key/value after the key in the array
     */
    private function insertAfter($position, $array, $key, $newValue)
    {
        if (array_key_exists($position, $array)) {
            $new = [];

            foreach ($array as $k => $value) {
                $new[$k] = $value;

                if ($k === $position) {
                    $new[$key] = $newValue;
                }
            }

            return $new;
        }
    }
}
