<?php

namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\RequestMiddlewareInterface;

/**
 *
 */
class Bootstrap implements RequestMiddlewareInterface
{
    /**
     * Service Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * JavaScript manager
     *
     * @var object
     */
    private $scripts;

    /**
     * Application settings
     *
     * @var array
     */
    private $settings;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->scripts = $container->get('scripts');
        $this->settings = $container->get('settings');
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
        // since there is no way for Slim to always have the latest request
        // availabe, this middleware attempts to remediate the problem via
        // assigning the latest router request on the container
        $this->container['router_request'] = $request;

        // check if the application is on debug state

        $debug = $this->settings['debug'];

        if ($debug) {
            $this->scripts->attach(['debug' => true]);
        }
    }
}
