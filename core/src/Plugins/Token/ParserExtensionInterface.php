<?php

namespace App\Plugins\Token;

/**
 *
 */
interface ParserExtensionInterface
{
    /**
     * Alters the passed body
     *
     * @param string $body
     */
    public function parse(&$body);
}
