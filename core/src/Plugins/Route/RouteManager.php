<?php

namespace App\Plugins\Route;

/**
 * Handles fetching of routes via configuration
 */
class RouteManager
{
    /**
     * Stores the defined routes
     *
     * @var array
     */
    private $routeList = [];

    /**
     * Public constructor
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->request = $container->get('raw_request');
        $this->configuration = $container->get('configuration_manager');
    }

    /**
     * Gets all list of defined routes
     */
    public function getRouteList()
    {
        if (empty($this->routeList)) {
            $values = $this->configuration->getConfiguration('routes');

            if (!empty($values['routes'])) {
                $this->routeList = $values['routes'];
            }
        }

        return $this->routeList;
    }

    /**
     * Gets a specific route
     */
    public function getRoute($path)
    {
        $routes = $this->getRouteList();

        return $routes[$path] ?? null;
    }

    /**
     * Gets a complete route configuration form a request object
     */
    public function getRouteConfiguration($request)
    {
        $dispatch = $this->router->dispatch($request);

        if (isset($dispatch[1])) {
            $route = $this->router->lookupRoute($dispatch[1]);
            $pattern = $route->getPattern();

            // capture our wildcard params with the proper route pattern
            $pattern = str_replace('{params: .*}', '*.*', $pattern);

            return $this->getRoute($pattern);
        }
    }

    /**
     *
     */
    public function getCurrentRouteConfiguration()
    {
        return $this->getRouteConfiguration($this->request);
    }

    /**
     * Get currenct route attributes
     *
     * @return array list of attributes
     */
    public function getAttributes($request = null)
    {
        if (empty($request)) {
            $request = $this->request;
        }

        $routeConfigs = $this->getRouteConfiguration($request);

        return $routeConfigs['attributes'] ?? null;
    }

    /**
     * Get currenct route attribute with selected key
     *
     * @param string $key
     *
     * @return any selected attribute
     */
    public function getAttribute($key, $request = null)
    {
        if (empty($request)) {
            $request = $this->request;
        }

        $attribute = $this->getAttributes($request);

        return $attribute[$key] ?? null;
    }
}
