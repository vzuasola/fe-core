<?php

namespace App\Url\Parser;

interface ParserInterface
{
    /**
     * Alters the URL by preparsing it
     *
     * @return string
     */
    public function parse($url);
}
