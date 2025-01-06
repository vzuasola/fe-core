<?php

namespace App\Plugins\ComponentWidget\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\RequestMiddlewareInterface;

class ComponentWidgetInit implements RequestMiddlewareInterface
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
    public function boot(RequestInterface &$request)
    {
    }

    /**
     *
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        $modules = $this->container->get('component_widget_manager')->getModuleList();

        foreach ($modules as $key => $module) {
            $instance = $module['instance_class'] ?? null;

            if ($instance) {
                if (method_exists($instance, 'processRequest')) {
                    $instance->processRequest($request, $response);
                }
            }
        }
    }
}
