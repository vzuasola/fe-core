<?php

namespace App\Middleware\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Slim\Http\Stream;

use App\Utils\LazyService;
use App\Middleware\Cache\ResponseCache;
use App\Plugins\Middleware\ResponseMiddlewareInterface;

/**
 * Middleware for replacing tokens
 */
class Token implements ResponseMiddlewareInterface
{
    /**
     * The not found handler
     */
    private $notFoundHandler;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->parser = $container->get('token_parser');
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $header = $response->getHeaderLine(ResponseCache::CACHE_HEADER);

        if ($header == ResponseCache::CACHE_MISS) {
            $this->handleReplacementsNonLazy($response);
        } else {
            $this->handleReplacements($response);
        }
    }

    /**
     *
     */
    private function handleReplacements(ResponseInterface &$response)
    {
        $raw = (string) $response->getBody();

        // filter function to escape a non HTML body (AJAX requests)
        $filter = function ($replacement) use ($raw) {
            if (!$this->isHTML($raw)) {
                $replacement = $this->escapeJson(trim($replacement));
            }

            return $replacement;
        };

        $filter->bindTo($this);

        $body = $this->parser->processTokens($raw, [
            'token_filter' => $filter,
        ]);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);

        $newStream = new Stream($stream);

        $response = $response->withBody($newStream);
    }

    /**
     *
     */
    private function handleReplacementsNonLazy(ResponseInterface &$response)
    {
        $raw = (string) $response->getBody();

        // filter function to escape a non HTML body (AJAX requests)
        $filter = function ($replacement) use ($raw) {
            if (!$this->isHTML($raw)) {
                $replacement = $this->escapeJson(trim($replacement));
            }

            return $replacement;
        };

        $filter->bindTo($this);

        $body = $this->parser->processTokens($raw, [
            'token_filter' => $filter,
            'lazy_only' => false,
            'skip_parsers' => true,
        ]);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);

        $newStream = new Stream($stream);

        $response = $response->withBody($newStream);
    }

    /**
     * Escape JSON
     */
    private function escapeJson($value)
    {
        $escapers = ["\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c"];
        $replacements = ["\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b"];

        return str_replace($escapers, $replacements, $value);
    }

    /**
     * Check if page is HTML
     */
    private function isHTML($text)
    {
        return strpos(ltrim($text), '<!DOCTYPE html') === 0;
    }
}
