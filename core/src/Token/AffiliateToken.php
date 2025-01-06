<?php

namespace App\Token;

use App\Drupal\Config;
use App\Middleware\Affiliates;
use Dflydev\FigCookies\Cookies;
use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the token that appends query string for legacy post login
 */
class AffiliateToken implements TokenInterface
{

    const AFFILIATE_VIEW = 'affiliates_rest_views';
    const AFFILIATE_CONFIG = 'affiliate_configuration';

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->request = $container->get('router_request');
        $this->views = $container->get('views_fetcher');
        $this->config = $container->get('config_fetcher');
        $this->playerSession = $container->get('player_session');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        $params = $this->processAffiliates();
        $query = http_build_query($params);

        if ($query) {
            if (!$this->playerSession->isLogin()) {
                $query = "?$query";
            } else {
                $query = "&$query";
            }
        }

        return $query;
    }

    /**
     * Add the affiliate cookies and return the valid
     * query parameters
     *
     * @return array
     */
    private function processAffiliates()
    {
        $validQueryParams = [];

        try {
            $result = $this->views->getViewById(self::AFFILIATE_VIEW);
            $config = $this->config->getGeneralConfigById(self::AFFILIATE_CONFIG);
            $groups = [];

            if ($result) {
                $groups = array_column($result, 'name');
                $groups = array_unique($groups);
            }

            $params = $this->request->getParams();
            $params = Config::lowercaseKeys($params);
            $lifetime = time() + (60 * $config['affiliate_expiration']);

            foreach ($groups as $group) {
                $group = strtolower($group);
                $temp = [];

                foreach ($result as $param) {
                    if (strtolower($param['name']) == $group) {
                        $paramName = strtolower($param['name_1']);
                        $cookieName = $group . 'Tracking';

                        // save the tracking codes from query params
                        if (isset($params[$paramName]) && !is_null($params[$paramName])) {
                            $temp[$paramName] = $params[$paramName];
                            $jsonEncoded = json_encode($temp);
                            setcookie($cookieName, $jsonEncoded, $lifetime, '/', '');

                            $validQueryParams[$paramName] = $params[$paramName];
                            break;
                        }

                        // if tracking code does not exist in query param
                        // get it from cookie
                        if (!isset($params[$paramName])) {
                            if (isset($_COOKIE[$cookieName])) {
                                $cookieParam = json_decode($_COOKIE[$cookieName]);

                                if (isset($cookieParam->$paramName)) {
                                    $validQueryParams[$paramName] = $cookieParam->$paramName;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return $validQueryParams;
    }
}
