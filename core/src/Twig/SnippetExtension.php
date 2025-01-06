<?php

namespace App\Twig;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Container\ContainerInterface;

/**
 * Provides snippet for css and js
 */
class SnippetExtension extends \Twig_Extension
{
    /**
     *
     */
    const TYPE_JAVASCRIPT = 'js';

    /**
     *
     */
    const TYPE_CSS = 'css';

    /**
     * Snippet fetcher
     *
     * @var object
     */
    private $snippetFetcher;

    /**
     * The current router request
     *
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * Public constructor.
     */
    public function __construct($snippetFetcher, ServerRequestInterface $request)
    {
        $this->snippetFetcher = $snippetFetcher;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('snippet_css', array($this, 'snippetCss'), array(
                'is_safe' => array('html'),
            )),
            new \Twig_SimpleFunction('snippet_js', array($this, 'snippetJs'), array(
                'is_safe' => array('html'),
            ))
        );
    }

    /**
     * Get CSS snippets
     *
     * @param string $position The snippet position on markup
     *
     * @return string
     */
    public function snippetCss($position)
    {
        $path = $this->getRequestPath();

        return $this->snippetFetcher->getSnippet($path, $this->snippetFetcher::TYPE_CSS, $position);
    }

    /**
     * Get JS snippets
     *
     * @todo add support for script source instead of actual code snippet4
     *
     * @param string $position The snippet position on markup
     *
     * @return string
     */
    public function snippetJs($position)
    {
        $path = $this->getRequestPath();

        $scripts = $this->snippetFetcher->getSnippet($path, $this->snippetFetcher::TYPE_JAVASCRIPT, $position);

        return $scripts;
    }

    /**
     * Get request path on URI
     *
     * @return string
     */
    private function getRequestPath()
    {
        $path = $this->request->getUri()->getPath();

        // trim preceeding slash
        return ltrim($path, '/');
    }
}
