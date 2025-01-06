<?php

namespace App\Player;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use App\Cookies\Cookies;
use App\Utils\Host;

/**
 * Cookie Session for player
 */
class CookieSession
{
    /**
     * Dynamic monolog logger object
     *
     * @var object
     */
    private $logger;

    /**
     * Request Object
     */
    private $request;

    /**
     * Cookie Service Object
     */
    private $cookieService;

    /**
     * JWT Service Object
     */
    private $jwt;

    /**
     * Constant variables
     */
    const ISSUER = 'webcomposer';
    const AUDIENCE = 'webcomposer';

    /**
     * Container resolver
     */
    public static function create($container)
    {
        return new static(
            $container->get('request'),
            $container->get('logger'),
            $container->get('cookie_service'),
            $container->get('jwt_encryption')
        );
    }

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param object
     */
    public function __construct($request, $logger, $cookieService, $jwt)
    {
        $this->request = $request;
        $this->logger = $logger;
        $this->cookieService = $cookieService;
        $this->jwt = $jwt;
    }


    /**
     * Set Cookie for DSB Authentication (will be used for wbc session sharing)
     *
     * @param string username
     * @param string playerId
     * @param string token
     * @param string currency
     */
    public function set($username, $playerId, $token, $currency)
    {
        try {
            $requestBody = [
                'username' => $username,
                'playerId' => $playerId,
                'sessionToken' => $token,
            ];

            $jwtOptions = [
                'issuer' => self::ISSUER,
                'audience' => self::AUDIENCE,
                'expire_time' => time() + 86400,
            ];

            $result = $this->cookieService->cut($requestBody);
            $wbcJwt = $this->jwt->encrypt($requestBody, $jwtOptions);

            $options = [
                'expire' => 0,
                'path' => '/',
                'domain' => Host::getDomain(),
                'secure' => strpos($this->request->getUri()->getBaseUrl(), 'https') === 0,
                'http' => false, // They need to read the cookie via javascript.
            ];

            Cookies::set('extToken', $result['jwt'], $options);
            Cookies::set('extCurrency', $currency, $options);
            Cookies::set('wbcToken', $wbcJwt, $options);
        } catch (\Exception $e) {
            $this->logger->error('Cookie set for session failed - ' . $e->getMessage());
            throw $e;
        }

        return $result;
    }

    /**
     * It sets the wbcToken cookie to be used by CookieAuthentication moddleware in case the PHP session cookie
     * is not there but the user is logged in. This is a limited version of set() menthod, to be used by Flutter
     * app when embedding our promotions listing page.
     *
     * @param $username
     * @param $playerId
     * @param $token
     * @return void
     * @throws \Exception
     */
    public function setWbc($username, $playerId, $token)
    {
        try {
            $requestBody = [
                'username' => $username,
                'playerId' => $playerId,
                'sessionToken' => $token,
            ];

            $jwtOptions = [
                'issuer' => self::ISSUER,
                'audience' => self::AUDIENCE,
                'expire_time' => time() + 86400,
            ];

            $wbcJwt = $this->jwt->encrypt($requestBody, $jwtOptions);

            $options = [
                'expire' => 0,
                'path' => '/',
                'domain' => Host::getDomain(),
                'secure' => strpos($this->request->getUri()->getBaseUrl(), 'https') === 0,
                'http' => false, // They need to read the cookie via javascript.
            ];

            Cookies::set('wbcToken', $wbcJwt, $options);
        } catch (\Exception $e) {
            $this->logger->error('Wbc cookie set for session failed - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete JWT cookie session
     */
    public function delete()
    {
        try {
            $options = [
                'path' => '/',
                'domain' => Host::getDomain(),
            ];

            Cookies::remove('extToken', $options);
            Cookies::remove('extCurrency', $options);
            Cookies::remove('wbcToken', $options);

            $this->logger->info('Cookie Session Removal successful');
        } catch (\Exception $e) {
            $this->logger->error('Cookie delete for session failed - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Read JWT cookie session
     */
    public function read()
    {
        $result = [];

        try {
            $cookie = Cookies::get('wbcToken');

            if (is_array($cookie)) {
                $cookie = $cookie[0];
            }

            if ($cookie && !empty($cookie)) {
                $options = [
                    'issuer' => self::ISSUER,
                    'audience' => self::AUDIENCE,
                ];

                $result = $this->jwt->decrypt($cookie, $options);
            }

            $this->logger->info('Cookie Get for session successful');
        } catch (\Exception $e) {
            $this->logger->info('Cookie Get for session failed - ' . $e->getMessage());
            throw $e;
        }

        return $result;
    }
}
