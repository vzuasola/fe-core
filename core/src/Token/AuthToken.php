<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the authentication token on the token system
 */
class AuthToken implements TokenInterface
{
    /**
     * Player Session
     *
     * @var object
     */
    private $playerSession;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->playerSession = $container->get('player_session');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        try {
            $ticket = $this->playerSession->getToken();
        } catch (\Exception $e) {
            $ticket = null;
        }

        return $ticket;
    }
}
