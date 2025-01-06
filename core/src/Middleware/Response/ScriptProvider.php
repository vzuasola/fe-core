<?php

namespace App\Middleware\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\ResponseMiddlewareInterface;

/**
 *
 */
class ScriptProvider implements ResponseMiddlewareInterface
{
    /**
     * Script provider manager
     *
     * @var object
     */
    private $providerManager;

    /**
     * Javascript manager
     *
     * @var object
     */
    private $scripts;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->providerManager = $container->get('script_provider_manager');
        $this->scripts = $container->get('scripts');
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $assets = [];
        $providers = $this->providerManager->getProviders();

        foreach ($providers as $provider) {
            $assets = array_replace_recursive($assets, $provider->getAttachments());
        }

        if ($assets) {
            $this->scripts->attach($assets);
        }
    }
}
