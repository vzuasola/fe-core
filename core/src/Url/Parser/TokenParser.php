<?php

namespace App\Url\Parser;

class TokenParser implements ParserInterface
{
    private $parser;

    /**
     * Public constructor.
     */
    public function __construct($parser)
    {
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($uri)
    {
        return $this->parser->processTokens($uri);
    }
}
