<?php

namespace App\Middleware\Request;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\Middleware\RequestMiddlewareInterface;

/**
 *
 */
class CaseInsensitiveRoute implements RequestMiddlewareInterface
{
    /**
     * Router manager
     */
    protected $router;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('route_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function boot(RequestInterface &$request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        $route = $this->router->getCurrentRouteConfiguration();
        $caseInsensitive = $route['case_insensitive']['enabled'] ?? true;
        $uri = $request->getUri();
        $path = strtolower($uri->getPath());

        if ($uri->getPath() != $path &&
            $request->getMethod() == 'GET' &&
            $caseInsensitive
        ) {
            $uri = $uri->withPath($path);
            $request = $request->withUri($uri);
        }
    }
}
