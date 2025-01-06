<?php

namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\RequestMiddlewareInterface;

/**
 * Strip all URI so that the application only detects routes without slashes
 */
class Slashes implements RequestMiddlewareInterface
{
    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
    }

    /**
     *
     */
    public function boot(RequestInterface &$request)
    {
    }

    /**
     *
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ($path != '/' && substr($path, -1) == '/') {
            $uri = $uri->withPath(substr($path, 0, -1));
            $request = $request->withUri($uri);
        }
    }
}
