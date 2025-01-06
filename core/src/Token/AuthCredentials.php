<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

use App\Legacy\LegacyEncryption;

/**
 * Exposes the authentication token on the token system
 */
class AuthCredentials implements TokenInterface
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
            $token = $this->playerSession->getToken();
            $enc = LegacyEncryption::encrypt($token);

            $ticket = "?token=$enc";
        } catch (\Exception $e) {
            $ticket = null;
        }

        return $ticket;
    }
}
