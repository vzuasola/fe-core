<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 *
 */
class Username implements TokenInterface
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
            $username = $this->playerSession->getUsername();
        } catch (\Exception $e) {
            $username = null;
        }

        return $username;
    }
}
