<?php

namespace App\Middleware\Cache;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Slim\Http\Stream;
use Slim\HttpCache\Cache;

use App\Plugins\Middleware\RequestMiddlewareInterface;
use App\Plugins\Middleware\ResponseMiddlewareInterface;

use App\Plugins\Middleware\TerminateAsCacheException;

/**
 * The page caching middleware
 */
class ResponseCache implements RequestMiddlewareInterface, ResponseMiddlewareInterface
{
    /**
     * The cache header for page hits and miss
     */
    const CACHE_HEADER = 'Page-Cache';

    /**
     * Flag if page cache should be skipped
     */
    const CACHE_SKIP = 'skip_page_cache';

    /**
     * Header for the timestamp for page cache
     */
    const CACHE_TIMESTAMP = 'page_cache_time';

    /**
     * Cache contexts for Cache hit and miss
     */
    const CACHE_HIT = 'Hit';
    const CACHE_MISS = 'Miss';
    const CACHE_INVALID = 'Miss-Invalid';

    /**
     * Response is not valid for caching, this is useful when certain responses that are not supposed to be empty.
     * but is cached anyway since it's response 200
     */
    const CACHE_RESPONSE_INVALID = 'response_cache_invalid';

    /**
     * Settings definition array
     *
     * @var array
     */
    private $settings;

    /**
     * Cacher object
     *
     * @var object
     */
    private $cacher;

    /**
     * Session handler object
     *
     * @var object
     */
    private $session;

    /**
     * Player session object
     *
     * @var object
     */
    private $playerSession;

    /**
     * Request object
     *
     * @var object
     */
    private $request;

    /**
     * Profiler object
     *
     * @var object
     */
    private $profiler;

    /**
     * Localization library
     */
    protected $localization;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     * @param array $pages Array of pages to activate caching
     * @param integer $expiration Default item expiration in seconds
     * @param boolean $isAnonymous Flag to check if it should be for anonymous users
     */
    public function __construct(ContainerInterface $container)
    {
        $this->settings = $container->get('settings');
        $this->cacher = $container->get('page_cache_adapter');
        $this->playerSession = $container->get('player_session');
        $this->session = $container->get('session');
        $this->request = $container->get('request');
        $this->profiler = $container->get('profiler');
        $this->router = $container->get('route_manager');
        $this->localization = $container->get('localization');
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
        $enabled = $this->settings['page_cache']['enable'] ?? false;

        if ($enabled &&
            $this->isCacheable($request) &&
            $this->isRouteCacheable()
        ) {
            $response = $this->getCacheResponses($request, $response);

            if ($response->getHeaderLine(self::CACHE_HEADER) == self::CACHE_HIT) {
                throw new TerminateAsCacheException($request, $response);
            }
        }
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $enabled = $this->settings['page_cache']['enable'] ?? false;

        if ($enabled &&
            $this->isCacheable($request) &&
            $this->isRouteCacheable()
        ) {
            $response = $this->setCacheResponses($request, $response);
        }
    }

    /**
     * Overridable Methods
     *
     */

    /**
     * Checks if the current state is cacheable
     */
    protected function isCacheable($request)
    {
        $skip = $request->getAttribute(self::CACHE_SKIP);
        $method = $this->request->getMethod();
        $hasFlashes = $this->session->hasFlashes();
        $isLogin = $this->playerSession->isLogin();
        $route = $this->router->getCurrentRouteConfiguration();
        $isFlutter = $this->request->getParam('flutterauth') ?? false;
        $isIcoreToken = $this->request->getParam('icoreToken') ?? false;
        $skipCachePages = ['promotions', 'contact_us'];
        $isSkipPage = ($isFlutter || $isIcoreToken)
            && in_array(($route['components']['main'] ?? false), $skipCachePages);

        if (($skip) || ($method !== 'GET' || $hasFlashes) || $isSkipPage) {
            // If request should be skipped
            return false;
        }

        if ($isLogin &&
            (isset($route['page_cache']['post_login']) && $route['page_cache']['post_login'])) {
            // If route is cacheable even in post-login
            // This is usually used for rest contents that are not bounded to user session
            return true;
        }

        // By default, page cache is disabled in post-login
        return !$isLogin;
    }

    /**
     * Gets the cache key
     */
    protected function getCacheKey($request)
    {
        $isMaintenance = $request->getAttribute('is_maintenance', false);
        $request = $request->getAttribute('override_request');

        if (empty($request)) {
            $request = $this->request;
        }

        $uri = $request->getUri()->getBaseUrl();
        $path = $request->getUri()->getPath();

        $alias = $uri . $path;

        if ($localized = $this->localization->getLocalLanguage()) {
            // Prepend the localized language to create a new cache key
            // We create a new cache key since this page "might" have localized content
            $alias = $localized . ':' . $alias;
        }

        if ($isMaintenance) {
            // We'll create a new cacheKey for the page that is in maintenance mode
            $alias = 'maintenance:' . $alias;
        }

        return md5(trim($alias, '/'));
    }

    /**
     * Check wheter the cache is valid or not
     *
     * @return boolean
     */
    protected function isCacheValid($item, $key)
    {
        return true;
    }

    /**
     * Check wheter the current route is cacheable
     *
     * @return boolean
     */
    protected function isRouteCacheable()
    {
        $route = $this->router->getCurrentRouteConfiguration();

        if (isset($route['page_cache']['enabled']) && $route['page_cache']['enabled']) {
            return true;
        }
    }

    /**
     * Getters and Setters
     *
     */

    /**
     * Get cached response
     *
     * @return object
     */
    private function getCacheResponses(
        RequestInterface &$request,
        ResponseInterface $response
    ) {
        $key = $this->getCacheKey($request);
        $item = $this->cacher->getItem(urlencode($key));

        if ($item->isHit() && $this->isCacheValid($item, $key)) {
            $data = $item->get();
            $response = $this->getResponseObjectFromCache($response, $data);

            $response = $response->withHeader(self::CACHE_HEADER, self::CACHE_HIT);

            // add a flag to the request object that we are on a cache hit state
            $request = $request->withAttribute(self::CACHE_HEADER, self::CACHE_HIT);

            $response = $this->setCacheResponseHeaders($response);
        } else {
            $response = $response->withHeader(self::CACHE_HEADER, self::CACHE_MISS);
        }

        return $response;
    }

    /**
     *
     */
    private function setCacheResponseHeaders($response)
    {
        // expire the cache via headers if the computation of the Redis
        // timeout and the elapsed time is less than zero
        //
        // remaining = max_timeout - (current_time - cache_time)
        $max = $this->settings['page_cache']['default_timeout'];
        $time = $response->getAttribute(self::CACHE_TIMESTAMP);
        $remaining = $max - (time() - $time);

        if ($this->settings['page_cache']['include_cache_control_headers']) {
            $directive = $this->settings['page_cache']['cache_control_directive'];
            $directive = str_replace(['$time'], [$remaining], $directive);

            $response = $response->withHeader('Cache-Control', $directive);
        }

        return $response;
    }

    /**
     * Set cached response
     *
     * @return object
     */
    private function setCacheResponses(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $key = $this->getCacheKey($request);
        $item = $this->cacher->getItem(urlencode($key));

        if (!$item->isHit()) {
            // Additional checking for custom response attribute to prevent saving of cache
            // This also checks if site encountered an error (4xx or 5xx) which will not trigger cache save
            if ($response->getAttribute(self::CACHE_RESPONSE_INVALID, false) === true
                || $response->getStatusCode() !== 200
            ) {
                return $response->withHeader(self::CACHE_HEADER, self::CACHE_INVALID);
            }

            $body = (string) $response->getBody();

            $response = $response->withAttribute(self::CACHE_TIMESTAMP, time());

            $data = [
                'body' => $body,
                'header' => $response->getHeaders(),
                'attributes' => $response->getAttributes(),
                'status' => $response->getStatusCode(),
            ];

            $item->set($data);

            $this->cacher->save($item, [
                'expires' => $this->getExpirationFromRoute(),
            ]);
        }

        return $response;
    }

    /**
     *
     */
    private function getExpirationFromRoute()
    {
        $route = $this->router->getCurrentRouteConfiguration();

        return $route['page_cache']['expires'] ?? $this->settings['page_cache']['default_timeout'] ?? 1000;
    }

    /**
     *
     */
    private function getResponseObjectFromCache($response, $cache)
    {
        $body = $cache['body'];
        $header = $cache['header'];
        $attributes = $cache['attributes'];
        $status = $cache['status'];

        // hack to make profiler work fine
        // will be fixed by Alex as soon as a I found out how to fix this
        $ms = $this->profiler->getRenderTime();
        $ms = $ms . ' (CACHE HIT) ';
        $body = preg_replace('/<span class="profiler-time-ms"\>(.*)\<\/span\>/', $ms, $body);

        $stream = $this->createStreamFromArray($body);
        $response = $response->withBody($stream);

        foreach ($header as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response->withAttributes($attributes)->withStatus($status);
    }

    /**
     *
     */
    private function createStreamFromArray($object)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $object);

        return new Stream($stream);
    }
}
