<?php

namespace App\Plugins\Token;

/**
 *
 */
interface TokenExtensionInterface
{
    /**
     * Alters the existing tokens
     */
    public function process(&$tokens);
}
