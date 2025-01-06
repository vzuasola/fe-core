<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

use App\Legacy\LegacyEncryption;

/**
 * Exposes the legacy to authenticate web composer via token credentials
 */
class LegacyRevampToken implements TokenInterface
{
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
        $ticket = null;

        try {
            $token = $this->playerSession->getToken();

            if ($token) {
                $ticket = LegacyEncryption::encrypt($token);
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return $ticket;
    }
}
