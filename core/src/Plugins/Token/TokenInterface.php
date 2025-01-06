<?php

namespace App\Plugins\Token;

/**
 *
 */
interface TokenInterface
{
    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options);
}
