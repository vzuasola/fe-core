<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 *
 */
class PartnerMatrixToken implements TokenInterface
{
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
        if ($this->playerSession->isLogin()) {
            $isPlayerAgent = $this->playerSession->getDetails()['isPlayerCreatedByAgent'] ?? false;

            if ($isPlayerAgent) {
                return " agent-player ";
            }
        }
    }
}
