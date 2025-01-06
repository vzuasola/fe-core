<?php

namespace App\Slim\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use Slim\Exception\NotFoundException;

/**
 * Handles middleware shutdown events
 */
class Shutdown
{
    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->notFoundHandler = $container->get('notFoundHandler');
    }

    /**
     *
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        // capture 404 errors so that 404 errors are able to stack up through the
        // middlewares
        try {
            $response = $next($request, $response);
        } catch (NotFoundException $e) {
            $handler = $this->notFoundHandler;
            $response = $handler($request, $response);
        }

        return $response;
    }
}
