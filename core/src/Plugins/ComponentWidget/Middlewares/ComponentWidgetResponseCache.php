<?php

namespace App\Plugins\ComponentWidget\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\ResponseMiddlewareInterface;

class ComponentWidgetResponseCache implements ResponseMiddlewareInterface
{
    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $modules = $this->container->get('component_widget_manager')->getModuleList();

        foreach ($modules as $key => $module) {
            $class = $module['class'];

            if ($class) {
                if (method_exists($class . 'Cache', 'processResponseCache')) {
                    $instance = $class . 'Cache';
                    $instance::processResponseCache($request, $response);
                }
            }
        }
    }
}
