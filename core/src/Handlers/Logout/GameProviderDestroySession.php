<?php

namespace App\Handlers\Logout;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class that handles the deauthentication of the game provider
 *
 * This get execute after logout
 */
class GameProviderDestroySession
{
    /**
     * Game provider manager
     */
    private $provider;

    /**
     * Public constructor
     */
    public function __construct($container)
    {
        $this->provider = $container->get('game_provider_manager');
    }

    /**
     *
     */
    public function __invoke($username)
    {
        $providers = $this->provider->getProviders();

        foreach ($providers as $provider) {
            $provider->onSessionDestroy($username);
        }
    }
}
