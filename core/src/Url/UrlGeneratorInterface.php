<?php

namespace App\Url;

use Psr\Http\Message\ServerRequestInterface;

interface UrlGeneratorInterface
{
    /**
     * Generate an URI path from the provided URI resource
     *
     * @param string $uri The URI resource
     * @param array $options Additional options
     *
     * @return string
     */
    public function generateUri($uri, $options);

    /**
     * Generate the canonical path from a URI
     *
     * @param ServerRequestInterface $request
     * @param string $uri The URI resource
     *
     * @return array
     */
    public function generateCanonicalsFromRequest(ServerRequestInterface $request, $uri);

    /**
     * Generate an URI path from the provided URI resource and request
     *
     * @param ServerRequestInterface $request
     * @param string $uri The URI resource
     * @param array $options Additional options
     *
     * @return string
     */
    public function generateFromRequest(ServerRequestInterface $request, $uri, $options);

    /**
     * Gets an alias from a given URI
     *
     * @param string $uri The URI resource
     *
     * @return string
     */
    public function getAliasFromUrl($url);

    /**
     * Determines whether a path is external
     *
     * @param string $path The URI resource
     *
     * @return boolean
     */
    public function isExternal($path);
}
