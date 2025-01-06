<?php

namespace App\Negotiation;

use Psr7Middlewares\Utils\NegotiateTrait;
use Psr7Middlewares\Utils\RedirectTrait;
use Psr7Middlewares\Utils\AttributeTrait;
use Psr7Middlewares\Utils\Helpers;
use Negotiation\LanguageNegotiator as Negotiator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

use App\Plugins\Middleware\TerminateException;
use App\Utils\Host;

/**
 * Handles custom URL negotiation
 *
 * Since this class is a fork of a vendor middleware, the logic of the
 * original class is still maintained (I don't like their implementation to be honest).
 * This class can be still made simpler.
 */
class LanguageNegotiation
{
    use NegotiateTrait;
    use RedirectTrait;
    use AttributeTrait;

    /**
     * The language index key
     */
    const LANGUAGE_KEY = 'LANGUAGE';

    /**
     * The product index key
     */
    const PRODUCT_KEY = 'PRODUCT';

    /**
     * The prefix index key
     */
    const PREFIX_KEY = 'PREFIX';

    /**
     * The exclusion index key
     */
    const EXCLUDE_KEY = 'EXCLUSION';

    /**
     * The exclusion prefix index key
     */
    const EXCLUDE_PREFIX_KEY = 'EXCLUSION_PREFIX';

    /**
     * Scripts class to add the language in JS
     */
    private $scripts;

    /**
     * Stores the list of available languages
     *
     * @var array
     */
    private $languages;

    /**
     * The default language
     *
     * @var string
     */
    private $default;

    /**
     * Stores the list of available products
     *
     * @var array
     */
    private $products;

    /**
     * Stores the default product
     *
     * @var string
     */
    private $defaultProduct;

    /**
     * Stores the list of path to be excluded
     *
     * @var array
     */
    private $exclusions;

    /**
     * The supply language code
     *
     * @var string
     */
    private $supplyLanguage;

    /**
     * Define the available languages.
     */
    public function __construct(
        $scripts,
        array $languages,
        $default = null,
        array $products = null,
        $defaultProduct = null,
        array $exclusions = null,
        $supplyLanguage = null
    ) {
        $this->scripts = $scripts;
        $this->languages = $languages;
        $this->default = $default;
        $this->products = $products;
        $this->defaultProduct = $defaultProduct;
        $this->exclusions = $exclusions;
        $this->supplyLanguage = $supplyLanguage;
    }

    /**
     * Execute the middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface &$request, ResponseInterface &$response)
    {
        // make adjustment to the request object to process the language prefix
        $language = $this->getLanguagePrefix($request);

        // make adjustment to the request object to process the product prefix
        $product = $this->getProductPrefix($request);

        // if language is not set, redirect to the proper URL with the language
        // parameters
        if (isset($this->products)) {
            // only redirect if not up from exclusion
            // substitute the product with a value if a request is excluded.
            // This is for URL generation to work flawlessly
            $substitute = $this->getExclusion($request, $language);
            if ($substitute) {
                $product = $substitute;
            }

            if (!isset($language) || !isset($product)) {
                // @hack if there is a substitute, nullify the product so it
                // redirects properly
                if ($substitute) {
                    $this->products = null;
                }

                $response = $this->setRedirection($request, $response);

                throw new TerminateException($request, $response);
            }
        } else {
            if (!isset($language) && $this->supplyLanguage) {
                $language = $this->supplyLanguage;

                $request = self::setAttribute($request, 'empty_language', true);
            }

            if (!isset($language)) {
                $response = $this->setRedirection($request, $response);

                throw new TerminateException($request, $response);
            }
        }

        // add path, language and product in Script attachments
        $this->scripts->attach([
            'lang' => $language,
            'defaultLang' => $this->default,
            'product' => $product,
            'defaultProduct' => $this->defaultProduct,
            'path' => $request->getUri()->getPath()
        ]);

        $request = $this->getFinalRequest($request, $response, $language, $product);
        $response = $this->getFinalResponse($response, $language, $product);

        $response = FigResponseCookies::set(
            $response,
            SetCookie::create('mhlanguage')
                ->withValue($language)
                ->withExpires(time() + 31556926)
                ->withPath('/')
                ->withdomain(Host::getDomain())
        );

        $this->finalizeResponse($response, $language, $product);

        return $response;
    }

    /**
     * Getters
     *
     */

    /**
     * Gets the language prefix from the request object
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function getLanguagePrefix(ServerRequestInterface &$request)
    {
        $uri = $request->getUri();

        $path = ltrim($uri->getPath(), '/');
        $dirs = explode('/', $path, 2);
        $language = strtolower(array_shift($dirs));

        if (!empty($language) && in_array($language, $this->languages, true)) {
            $request = $request->withUri($uri->withPath('/' . array_shift($dirs)));

            return $language;
        }
    }

    /**
     * Gets the product prefix from the request object
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function getProductPrefix(ServerRequestInterface &$request)
    {
        if ($this->products) {
            $uri = $request->getUri();

            $path = ltrim($uri->getPath(), '/');
            $dirs = explode('/', $path, 2);
            $product = strtolower(array_shift($dirs));

            if (!empty($product) && in_array($product, $this->products, true)) {
                $request = $request->withUri($uri->withPath('/' . array_shift($dirs)));

                return $product;
            }
        }
    }

    /**
     * Exclusions
     *
     */

    /**
     * Check if a path is up for exclusion
     */
    private function getExclusion(&$request, $language)
    {
        $clone = clone $request;

        $this->getLanguagePrefix($clone);

        $path = $clone->getUri()->getPath();
        $path = trim($path, '/');

        $exclusion = array_keys($this->exclusions);

        if (in_array($path, $exclusion)) {
            $exclusion = $this->exclusions[$path];
            $request = self::setAttribute($request, self::EXCLUDE_KEY, $path);
            $request = self::setAttribute($request, self::EXCLUDE_PREFIX_KEY, $language);

            return $exclusion;
        }
    }

    /**
     * Redirections
     *
     */

    /**
     * Redirects to the proper URL format
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface $response
     */
    private function setRedirection(ServerRequestInterface $request, ResponseInterface $response)
    {
        $uri = $request->getUri();

        $language = $this->default;

        // if language is empty, use the default one, or just use no language at
        // all
        if (empty($language)) {
            $language = $this->default ?? '';
        }

        if (!empty($this->products)) {
            // get the first product as the default product
            $product = reset($this->products);
            $path = Helpers::joinPath($language, $product, $uri->getPath());
        } else {
            $path = Helpers::joinPath($language, $uri->getPath());
        }

        return $this->getRedirectResponse($request, $uri->withPath($path), $response);
    }

    /**
     * Finalization
     *
     */

    /**
     * Gets the final request to be passed to the next middleware
     *
     * @param ServerRequestInterface $request
     * @param string                 $language
     * @param string                 $product
     *
     * @return ServerRequestInterface $request
     */
    private function getFinalRequest(
        ServerRequestInterface $request,
        ResponseInterface &$response,
        $language,
        $product
    ) {
        $request = self::setAttribute($request, self::LANGUAGE_KEY, $language);

        $response = $response->withAttribute('language', $language);

        if ($product) {
            $request = self::setAttribute($request, self::PRODUCT_KEY, $product);
            $request = self::setAttribute($request, self::PREFIX_KEY, "$language/$product");

            $response = $response->withAttribute('product', $product)
                ->withAttribute('prefix', "$language/$product");
        } else {
            $request = self::setAttribute($request, self::PREFIX_KEY, $language);

            $response = $response->withAttribute('prefix', $language);
        }

        return $request;
    }

    /**
     * Gets the final response to be passed to the next middleware
     *
     * @param ResponseInterface $response
     * @param string            $language
     * @param string            $product
     *
     * @return ResponseInterface $response
     */
    private function getFinalResponse(ResponseInterface $response, $language, $product)
    {
        $response = $response->withHeader('Content-Language', $language);

        if ($product) {
            $response = $response->withHeader('Content-Product', $product);
        }

        return $response;
    }

    /**
     * Apply response headers to be passed to the client browser
     *
     * @param ResponseInterface $response
     * @param string            $language
     * @param string            $product
     */
    private function finalizeResponse(ResponseInterface &$response, $language, $product)
    {
        if (!$response->hasHeader('Content-Language')) {
            $response = $response->withHeader('Content-Language', $language);
        }

        if ($product && !$response->hasHeader('Content-Product')) {
            $response = $response->withHeader('Content-Product', $product);
        }
    }
}
