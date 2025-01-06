<?php

namespace App\Middleware\Request;

use App\Drupal\Config;
use App\Cookies\Cookies;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use App\Plugins\Middleware\RequestMiddlewareInterface;

/**
 *
 */
class Tracking implements RequestMiddlewareInterface
{
    const AFFILIATE_VIEW = 'affiliates';
    const AFFILIATE_CONFIG = 'affiliate_configuration';

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
    public function boot(RequestInterface &$request)
    {
    }

    /**
     *
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        // temporary, due to a race condition somewhere in the container,
        // some dependency cannot be resolved immediately
        $this->resolveDependencies();

        $config = $this->settings['tracking'];

        if (!empty($config['enable'])) {
            try {
                $cookies = $this->getCookies();
                $affiliates = $this->views->getViewById(self::AFFILIATE_VIEW);
                $config = $this->config->getGeneralConfigById(self::AFFILIATE_CONFIG);

                // put affiliates as part of request
                $response = $response->withAttribute('affiliates', $affiliates);

                $params = $this->request->getParams();

                $lowercaseAffiliates = Config::lowercaseKeys($affiliates);
                foreach ($params as $key => $value) {
                    $key = strtolower($key);
                    if (isset($lowercaseAffiliates[$key])) {
                        $cookies[$key] = $value;
                    }
                }

                if (!empty($cookies) && http_build_query($cookies) !== Cookies::get('affiliates')) {
                    $lifetime = time() + (60 * $config['affiliate_expiration']);
                    Cookies::set('affiliates', http_build_query($cookies), [
                        'expire' => $lifetime,
                        'http' => false,
                        'path' => '/',
                        'secure' => true,
                        'samesite' => 'None'
                    ]);
                }
            } catch (\Exception $e) {
                // do nothing
            }
        }
    }

    /**
     *
     */
    private function resolveDependencies()
    {
        $this->request = $this->container->get('router_request');
        $this->views = $this->container->get('views_fetcher');
        $this->config = $this->container->get('config_fetcher');
        $this->settings = $this->container->get('settings');
    }

    /**
     *
     */
    private function getCookies()
    {
        $cookies = [];
        $store = Cookies::get('affiliates');

        if (!empty($store)) {
            try {
                // Check the affiliates cookie if it's an array
                // This will prevent any PHP warning
                $store = is_array($store) ? $store[0] : $store;

                parse_str($store, $output);
                $cookies = $output;
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $cookies;
    }
}
