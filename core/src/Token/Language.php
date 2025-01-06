<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the language on the token system
 */
class Language implements TokenInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->lang = $container->get('lang');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        return $this->lang;
    }
}
