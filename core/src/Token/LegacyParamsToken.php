<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the token that appends query string for legacy post login
 */
class LegacyParamsToken implements TokenInterface
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
            $details = $this->playerSession->getDetails();

            $playerId = $details['playerId'] ?? null;
            $language = $details['locale'] ?? null;

            $query = [
                'credentials' => $credentials,
                'token' => $token,
                'playerID' => $playerId,
                'language' => $language,
            ];

            if ($query = http_build_query($query)) {
                $ticket = '?' . $query;
            } else {
                $ticket = $query;
            }
        } catch (\Exception $e) {
            $ticket = null;
        }

        return $ticket;
    }
}
