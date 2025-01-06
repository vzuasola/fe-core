<?php

namespace App\Middleware\Cache;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use Psr7Middlewares\Utils\AttributeTrait;

use App\Plugins\Middleware\ResponseMiddlewareInterface;
use App\Negotiation\LanguageNegotiation;

/**
 *
 */
class Bootstrap implements ResponseMiddlewareInterface
{
    use AttributeTrait;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $language = $response->getAttribute('language');
        $prefix = $response->getAttribute('prefix');

        // since this is a cached response, the language middleware won't run, so we supply
        // the language and product attributes to the correct value fetched from the
        // cached response

        $request = self::setAttribute($request, LanguageNegotiation::LANGUAGE_KEY, $language);
        $request = self::setAttribute($request, LanguageNegotiation::PREFIX_KEY, $language);

        $product = $response->getAttribute('product');

        if ($product) {
            $request = self::setAttribute($request, LanguageNegotiation::PRODUCT_KEY, $language);
        }

        $this->container['router_request'] = $request;
    }
}
