<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the token that appends query string for legacy post login
 */
class CredentialsToken implements TokenInterface
{
    /**
     * Legacy authentication
     *
     * @var object
     */
    private $legacyAuthentication;

    /**
     * Player Session
     *
     * @var object
     */
    private $playerSession;

    /**
     * User fetcher
     *
     * @var object
     */
    private $user;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->legacyAuthentication = $container->get('legacy_authentication');
        $this->playerSession = $container->get('player_session');
        $this->user = $container->get('user_fetcher');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        try {
            $credentials = $this->legacyAuthentication->getAuthenticationToken();
            $token = $this->playerSession->getToken();

            $query = [
                'credentials' => $credentials,
                'token' => $token,
            ];

            $details = $this->playerSession->getDetails();

            if (isset($details['playerId'])) {
                $query['playerID'] = $details['playerId'];
            }

            if (isset($details['local'])) {
                $query['language'] = $details['local'];
            }

            $ticket = http_build_query($query);
        } catch (\Exception $e) {
            $ticket = null;
        }

        return $ticket;
    }
}
