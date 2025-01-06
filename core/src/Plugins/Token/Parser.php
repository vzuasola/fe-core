<?php

namespace App\Plugins\Token;

/**
 * Handles converting string with tokens to string with actual token values
 */
class Parser
{
    /**
     * The token manager
     *
     * @param $object $tokenManager
     */
    protected $tokenManager;

    /**
     * List of parser instances
     *
     * @var array
     */
    private $parsers = [];

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct($tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * Set a new parser
     *
     * @param ParserInterface $parser A parser object
     */
    public function setParser(ParserExtensionInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    /**
     * Process tokens
     *
     * @param string $body
     * @param array $options Additional options
     *    Available options
     *        boolean `skip_parsers` If specified will exclude running the parsers
     *            Parsers are extension of the token system.
     *        boolean `lazy_only` If specified will toggle if lazy tokens will only be parsed
     *            or lazy tokens will be excluded
     *        closure `token_filter` Accepts closure of with arguments
     *            $replacement The replacement text
     *            $key The token key the replacement was intended
     *
     *            The closure should return the new replacement
     */
    public function processTokens($body, $options = [])
    {
        $tokens = $this->tokenManager->getTokenList();

        if (isset($options['lazy_only'])) {
            if ($options['lazy_only']) {
                $tokens = $this->tokenManager->getLazyTokens();
            } else {
                $tokens = $this->tokenManager->getNonLazyTokens();
            }
        }

        $this->parseTokens($tokens, $body, $options);

        return $body;
    }

    /**
     * Parse the tokens to the response body
     *
     * @param boolean $encode Wheter to encode a the replacements (for JSON consumption)
     */
    private function parseTokens($tokens, &$body, $options)
    {
        $callback = function ($matches) use ($tokens, $options) {
            if (count($matches) == 2) {
                list($replacement, $key) = $matches;

                if (isset($tokens[$key])) {
                    $replacement = $this->tokenManager->getToken($tokens[$key], ['key' => $key]);

                    if (isset($options['token_filter']) &&
                        is_callable($options['token_filter'])
                    ) {
                        $filter = $options['token_filter'];
                        $replacement = $filter($replacement, $key);
                    }
                }

                return $replacement;
            }
        };

        $callback->bindTo($this);

        $body = preg_replace_callback("/\{([^ \n\r\"]*?)\}/", $callback, $body);

        if (isset($options['skip_parsers']) && $options['skip_parsers']) {
            // do not parse anything
        } else {
            // check if there are any extensions then execute them
            foreach ($this->parsers as $parser) {
                $parser->parse($body);
            }
        }
    }
}
