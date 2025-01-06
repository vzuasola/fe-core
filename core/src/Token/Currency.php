<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 *
 */
class Currency implements TokenInterface
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
            $playerInfo = $this->playerSession->getDetails();
            $currency = $playerInfo['currency'] ?? null;
        } catch (\Exception $e) {
            $currency = null;
        }

        return $currency;
    }
}
