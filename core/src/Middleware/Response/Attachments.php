<?php

namespace App\Middleware\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Slim\Http\Stream;

use App\Negotiation\PathNegotiator;
use App\Plugins\Middleware\ResponseMiddlewareInterface;
use App\Utils\LazyService;

/**
 *
 */
class Attachments implements ResponseMiddlewareInterface
{
    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->views = LazyService::createLazyDependency($container, 'view');
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $this->processReplacements($response);
    }

    /**
     *
     */
    private function processReplacements(&$response)
    {
        $body = (string) $response->getBody();

        $script = $this->views->fetch('@base/components/scripts/attachments.html.twig');
        $includes = $this->views->fetch('@base/components/scripts/includes.html.twig');

        $body = str_replace(
            ['<#SCRIPT#>', '<#SCRIPT_INCLUDES#>'],
            [$script, $includes],
            $body
        );

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);

        $newStream = new Stream($stream);

        $response = $response->withBody($newStream);
    }
}
