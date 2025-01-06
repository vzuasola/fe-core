<?php

namespace App\Middleware\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\ResponseMiddlewareInterface;

/**
 *
 */
class Headers implements ResponseMiddlewareInterface
{
    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('route_manager');
        $this->raw = $container->get('raw_request');
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $route = $this->router->getRouteConfiguration($this->raw);

        if (isset($route['headers']) && is_array($route['headers'])) {
            foreach ($route['headers'] as $header => $value) {
                if ($response->getAttribute("headers-no-override:$header")) {
                    // special flag to prevent override of headers
                    // we will do nothing at this point
                } else {
                    $response = $response->withHeader($header, $value);
                }
            }
        }
    }
}
