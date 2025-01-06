<?php

namespace App\Negotiation;

use Psr7Middlewares\Utils\AttributeTrait;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Utility class to get the current negotiation values
 */
class PathNegotiator
{
    use AttributeTrait;

    /**
     * Default language code
     */
    const DEFAULT_LANGUAGE = 'en';

    /**
     * Returns the language.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public static function getLanguage(ServerRequestInterface $request)
    {
        return self::getAttribute($request, LanguageNegotiation::LANGUAGE_KEY);
    }

    /**
     * Returns the product.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public static function getProduct(ServerRequestInterface $request)
    {
        return self::getAttribute($request, LanguageNegotiation::PRODUCT_KEY);
    }

    /**
     * Returns the URL prefix.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public static function getPrefix(ServerRequestInterface $request)
    {
        return self::getAttribute($request, LanguageNegotiation::PREFIX_KEY);
    }

    /**
     * Gets a custom data attribute
     *
     * @param ServerRequestInterface $request
     * @param string $key
     *
     * @return string
     */
    public static function getCustom(ServerRequestInterface $request, $key)
    {
        return self::getAttribute($request, $key);
    }
}
