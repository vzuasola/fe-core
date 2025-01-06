<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the legacy credentials on the token system
 */
class LegacyToken implements TokenInterface
{
    /**
     * Legacy authentication
     *
     * @var object
     */
    private $legacyAuthentication;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->legacyAuthentication = $container->get('legacy_authentication');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        try {
            $ticket = $this->legacyAuthentication->getAuthenticationToken();
        } catch (\Exception $e) {
            $ticket = null;
        }

        return $ticket;
    }
}
