<?php

namespace App\Middleware\Cache;

use App\Drupal\Config;
use App\Cookies\Cookies;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use App\Plugins\Middleware\ResponseMiddlewareInterface;

/**
 *
 */
class LazyTracking implements ResponseMiddlewareInterface
{
    const AFFILIATE_CONFIG = 'affiliate_configuration';

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->request = $container->get('request');
        $this->settings = $container->get('settings');
        $this->config = $container->get('config_fetcher');
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        $config = $this->settings['tracking'];

        if (!empty($config['enable'])) {
            try {
                $cookies = $this->getCookies();
                $affiliates = $response->getAttribute('affiliates');
                $config = $this->config->getGeneralConfigById(self::AFFILIATE_CONFIG);

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
    private function getCookies()
    {
        $cookies = [];
        $store = Cookies::get('affiliates');

        if (!empty($store)) {
            try {
                parse_str($store, $output);
                $cookies = $output;
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $cookies;
    }
}
