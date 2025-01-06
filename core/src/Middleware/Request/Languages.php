<?php

namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Negotiation\LanguageNegotiation;
use App\Negotiation\Exception\InvalidLanguageException;
use App\Plugins\Middleware\RequestMiddlewareInterface;

/**
 * The main language bootstrap middleware
 */
class Languages implements RequestMiddlewareInterface
{
    /**
     * Language fetcher sevice
     */
    private $languageFetcher;

    /**
     * Scripts class for adding language to JS
     */
    private $scripts;

    /**
     * The monolog logger object
     *
     * @var object
     */
    private $logger;

    /**
     * Array of supported products
     *
     * @var array
     */
    private $products;

    /**
     * Prefix flag
     *
     * @var bool
     */
    private $prefixed;

    /**
     * Default product
     *
     * @var string
     */
    private $defaultProduct;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->languageFetcher = $container->get('language_fetcher');
        $this->scripts = $container->get('scripts');
        $this->logger = $container->get('logger');

        $this->products = $container->get('settings')['product_url'];
        $this->exclusion = $container->get('settings')['product_exclusions'] ?? [];
        $this->prefixed = $container->get('settings')['asset']['prefixed'];
        $this->whitelisted = $container->get('settings')['prefix_exclusion'];
        $this->supplyLanguage = $container->get('settings')['supply_language_on_empty'] ?? false;

        $this->defaultProduct = $container->get('product_default');
    }

    /**
     *
     */
    public function boot(RequestInterface &$request)
    {
    }

    /**
     *
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        // if page is whitelisted, then don't add prefix on it
        if ($this->isWhitelisted($request, $this->whitelisted)) {
            return;
        }

        $languages = $this->getLanguageSettings();
        $default = reset($languages);

        $products = $this->products;

        if ($products) {
            $products = (array) $products;
        }

        $this->scripts->attach([
            'prefixed' => $this->prefixed
        ]);

        $middleware = (
            new LanguageNegotiation(
                $this->scripts,
                $languages,
                $default,
                $products,
                $this->defaultProduct,
                $this->exclusion,
                $this->supplyLanguage
            )
        )->redirect();

        $response = $middleware($request, $response);
    }

    /**
     * Gets the language mapping
     *
     * @return array
     */
    private function getLanguageSettings()
    {
        try {
            $language = $this->languageFetcher->getLanguages();
        } catch (\Exception $e) {
            throw $e;
        }

        // capture invalid redirects
        foreach ($language as $definition) {
            if (empty($definition['id']) || empty($definition['prefix'])) {
                $this->logger->critical('invalid_language_config', [
                    'message' => 'There is a language without an ID or prefix'
                ]);

                throw new \Exception(
                    'Invalid language configuration. There is a language without an ID or prefix.'
                );
            }
        }

        $default = $language['default']['id'];

        // remove the default language from the response
        unset($language['default']);

        $this->moveArrayToTop($language, $default);

        return array_column($language, 'prefix');
    }

    /**
     * Check if pages should be whitelisted
     *
     * @param array $pages
     *
     * @return boolean
     */
    public function isWhitelisted($request, $pages)
    {
        $path = $request->getUri()->getPath();
        $path = trim(trim($path, '/'));

        if ($pages) {
            foreach ($pages as $page) {
                $pagematch = trim(trim($page, '/'));

                if (fnmatch($pagematch, $path)) {
                    return $page;
                }
            }
        }
    }

    /**
     * Helper method to move array to top element
     */
    private function moveArrayToTop(array &$array, $key)
    {
        $temp = array($key => $array[$key]);
        unset($array[$key]);
        $array = $temp + $array;
    }
}
