<?php

namespace App\Slim\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\RequestMiddlewareInterface;
use App\Plugins\Middleware\ResponseMiddlewareInterface;
use App\Plugins\Middleware\TerminateAsCacheException;
use App\Plugins\Middleware\TerminateException;

/**
 * Handles invokation of request and response middlewares
 */
class Init
{
    /**
     * Public constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $container->get('middleware_manager');
    }

    /**
     *
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, $next)
    {
        $requestWares = $this->manager->getRequestMiddlewares();
        $responseWares = $this->manager->getResponseMiddlewares();

        // Boot all request middlewares
        foreach ($requestWares as $middleware) {
            $instance = new $middleware($this->container);
            $instance->boot($request);
        }

        // Execute the request middlewares
        foreach ($requestWares as $middleware) {
            $instance = new $middleware($this->container);

            \App\Kernel::profiler()->setMessage($middleware, 'Request Middlewares');

            try {
                $instance->handleRequest($request, $response);
            } catch (TerminateException $e) {
                return $e->getResponse();
            } catch (TerminateAsCacheException $e) {
                return $this->handleCache($e);
            }
        }

        // log response middlewares
        foreach ($responseWares as $middleware) {
            \App\Kernel::profiler()->setMessage($middleware, 'Response Middlewares');
        }

        // Run the controllers
        $response = $next($request, $response);

        // Execute all response middlewares
        foreach ($responseWares as $middleware) {
            $instance = new $middleware($this->container);

            try {
                $instance->handleResponse($request, $response);
            } catch (TerminateException $e) {
                return $e->getResponse();
            } catch (TerminateAsCacheException $e) {
                return $this->handleCache($e);
            }
        }

        return $response;
    }

    /**
     * Gets the middleware instance of a forward request
     */
    private function handleCache($e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();

        $middlewares = $this->manager->getCacheMiddlewares();

        foreach ($middlewares as $middleware) {
            $instance = new $middleware($this->container);
            $instance->handleResponse($request, $response);
        }

        return $response;
    }
}
