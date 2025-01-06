<?php

namespace App\Middleware\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\ResponseMiddlewareInterface;

/**
 *
 */
class GameProvider implements ResponseMiddlewareInterface
{
    /**
     * Game provider manager
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
        $this->providerManager = $container->get('game_provider_manager');
        $this->scripts = $container->get('scripts');
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $providers = $this->providerManager->getProviders();

        // loop through the active game providers and attach the JS files
        foreach ($providers as $provider) {
            // initialize provider
            $provider->init($request, $response);

            $assets = $provider->getJavascriptAssets();

            if ($assets) {
                foreach ($assets as $file) {
                    $this->scripts->add($file);
                }
            }
        }
    }
}
