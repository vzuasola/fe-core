<?php

namespace App\Handlers\Login;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class that handles the authentication of the game provider
 *
 * This get execute after successful login
 */
class GameProviderAuthenticate
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
    public function __invoke($username, $password)
    {
        $providers = $this->provider->getProviders();

        foreach ($providers as $provider) {
            $provider->authenticate($username, $password);
        }
    }
}
