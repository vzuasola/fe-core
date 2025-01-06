<?php

namespace App\Url;

use App\Url\Parser\ParserInterface;

/**
 * The URL Base class
 */
abstract class Base
{
    private $parsers = [];

    /**
     * Add a new parser
     *
     * @param ParserInterface $parser A parser object
     */
    public function setParser(ParserInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    protected function doParse($url)
    {
        foreach ($this->parsers as $parser) {
            $url = $parser->parse($url);
        }

        return $url;
    }
}
