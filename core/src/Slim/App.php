<?php

namespace App\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Slim\App as Base;
use App\Utils\Sort;

/**
 * Slim App extension
 */
class App extends Base
{
    /**
     * Middleware weights
     */
    const LATE = 0;
    const NORMAL = 1;
    const EARLY = 2;
    const EARLIEST = 3;

    /**
     * Stores the application middlewares
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * List of allowed methods
     *
     * @var array
     */
    protected $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Stores the application routes
     *
     * @var array
     */
    protected $routes = [];

    /**
     * {@inheritdoc}
     */
    public function run($silent = false)
    {
        $this->processMiddlewares();
        $this->processConfigureMiddlewares();
        $this->processConfigureRoutes();

        parent::run($silent);
    }

    /**
     * Middleware Overrides
     *
     */

    /**
     * Provides a way to add middlewares with weight
     */
    public function add($callable, $weight = self::NORMAL)
    {
        switch ($weight) {
            case self::EARLIEST:
            case self::EARLY:
            case self::NORMAL:
            case self::LATE:
                if ($callable instanceof \Closure) {
                    $this->middlewares[] = ['weight' => $weight, 'callable' => $callable];
                } else {
                    $name = get_class($callable);
                    $this->middlewares[$name] = ['weight' => $weight, 'callable' => $callable];
                }

                break;

            default:
                throw new KernelException('Added invalid weight for middleware');
        }
    }

    /**
     * Append all middleware to the Slim application
     *
     * @deprecated To be removed in favor of middleware.yml
     */
    private function processMiddlewares()
    {
        uasort($this->middlewares, Sort::class . '::sort');

        foreach ($this->middlewares as $middleware) {
            parent::add($middleware['callable']);
        }
    }

    /**
     * Process configured middlewares
     */
    private function processConfigureMiddlewares()
    {
        $container = $this->getContainer();

        parent::add(new \App\Slim\Middleware\Shutdown($container));
        parent::add(new \App\Slim\Middleware\Init($container));
    }

    /**
     * Routing Overrides
     *
     */

    /**
     * Provides a way to add overridable routes
     */
    public function map(array $methods, $pattern, $callable)
    {
        $this->routes[$pattern] = func_get_args();
    }

    /**
     * Process routes from the configuration files
     */
    private function processConfigureRoutes()
    {
        $routes = $this->getRoutes();

        foreach ($routes as $pattern => $definition) {
            $methods = (array) $definition['method'];
            $callable = $definition['action'];

            // if method is ANY, supply all possible methods
            if ($definition['method'] == 'ANY') {
                $methods = $this->methods;
            }

            // capture our wildcard params with the proper route pattern
            $pattern = str_replace('*.*', '{params: .*}', $pattern);

            $route = parent::map($methods, $pattern, $callable);

            if (!empty($definition['middlewares'])) {
                $this->processRouteMiddlewares($route, $definition['middlewares']);
            }
        }
    }

    /**
     * Get the valid list of routes
     */
    private function getRoutes()
    {
        $manager = $this->getContainer()->get('route_manager');
        $routes = $manager->getRouteList();

        // handling for routes.php
        // will be deprecated soon
        foreach ($this->routes as $route) {
            list($method, $pattern, $callable) = $route;

            $routes[$pattern]['method'] = $method;
            $routes[$pattern]['action'] = $callable;
        }

        return $routes;
    }

    /**
     * Process route middlewares
     */
    private function processRouteMiddlewares($route, array $middlewares)
    {
        // define the replacement variable for middleware arguments
        $replacements = [
            '@container' => $this->getContainer(),
        ];

        foreach ($middlewares as $class => $arguments) {
            $args = [];

            if ($arguments) {
                // service container resolution
                if (is_array($arguments)) {
                    foreach ($arguments as $arg) {
                        if (is_string($arg) && isset($replacements[$arg])) {
                            $args[] = $replacements[$arg];
                        } else {
                            $args[] = $arg;
                        }
                    }
                }

                $instance = new $class(...$args);
                $route->add($instance);
            }
        }
    }

    /**
     *
     */
    protected function handlePhpError(\Throwable $e, ServerRequestInterface $request, ResponseInterface $response)
    {
        $settings = $this->getContainer()->get('settings');

        if (empty($settings['error_handler']['type'])) {
            return;
        }

        if ($settings['error_handler']['type'] == 'monolog') {
            $channel = $this->getContainer()->get('logger');
            $monolog = $channel($settings['error_handler']['options']['channel']);

            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

            $monolog->log(\Psr\Log\LogLevel::CRITICAL, $e->getMessage(), $data);
        }

        parent::handlePhpError($e, $request, $response);
    }
}
