<?php

namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

use App\Plugins\Middleware\RequestMiddlewareInterface;
use App\Plugins\Middleware\TerminateException;
use App\SSO\SSOInterface;
use App\Utils\Host;

/**
 * Handles the SSO behavior and provides data to underlying SSO Javascript
 */
class SSO implements RequestMiddlewareInterface
{
    /**
     * JavaScript manager
     *
     * @var object
     */
    private $scripts;

    /**
     * Application settings
     *
     * @var array
     */
    private $settings;

    /**
     * Config fetcher
     *
     * @var object
     */
    private $config;

    /**
     * Logger manager
     *
     * @var object
     */
    private $logger;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->scripts = $container->get('scripts');
        $this->settings = $container->get('settings');
        $this->sso = $container->get('session_sso');
        $this->request = $container->get('request');
        $this->logger = $container->get('logger');
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
        $config = $this->settings['sso'];

        if (!empty($config['enable'])) {
            $server = $this->sso->getServerUri();

            if ($server) {
                $this->attachScript($server);

                if ($redirect = $this->checkCookie($request, $response)) {
                    throw new TerminateException($request, $redirect);
                }

                if ($redirect = $this->checkRedirection($request, $response, $server)) {
                    throw new TerminateException($request, $redirect);
                }
            } else {
                $this->logger->critical('sso', [
                    'message' => 'SSO is enabled but no valid sso server configured',
                    'server' => $server,
                ]);
            }
        }
    }

    /**
     * Tell the Javascript SSO implementation to start listening to the server
     */
    private function attachScript($server)
    {
        $this->scripts->attach([
            'sso_enable' => true,
            'sso_endpoint' => $server,
            'sso_domain' => Host::getHostnameFromUri($server),
        ]);
    }

    /**
     * Check if I have been redirected from the server, and the server is attempting
     * to synchronize a token with me
     */
    private function checkCookie($request, $response)
    {
        $uri = $this->request->getUri();
        $sessionId = $request->getQueryParam(SSOInterface::QUERY);

        if (!empty($sessionId)) {
            $host = $uri->getBaseUrl();
            $path = $uri->getPath();
            $query = $this->request->getQueryParams();

            unset($query[SSOInterface::QUERY]);

            $queryString = http_build_query($query);
            $redirect = "$host$path";

            if (!empty($queryString)) {
                $redirect = "$redirect?$queryString";
            }

            $this->sso->setClientIdentifier($sessionId);

            return $response->withStatus(302)->withHeader('Location', $redirect);
        }
    }

    /**
     * Checks if the client needs to redirect to the server
     */
    private function checkRedirection($request, $response, $server)
    {
        $uri = $this->request->getUri();

        $serverDomain = Host::getHostnameFromUri($server);
        $myDomain = Host::getHostnameFromUri($uri->getBaseUrl());

        // checks if I am the server
        $isServer = trim($serverDomain, '/') === trim($myDomain, '/');

        // checks if I am already authenticated with server (flag exists)
        $isCookieSet = FigRequestCookies::get($request, SSOInterface::COOKIE)->getValue();

        if (!$isServer && !$isCookieSet) {
            $scheme = $uri->getScheme();
            $host = $uri->getHost();
            $path = $uri->getPath();
            $query = $uri->getQuery();

            $redirect = "$server/api/sso/redirect?sso-scheme=$scheme&sso-path=$host$path";

            if ($query) {
                $redirect = "$redirect&$query";
            }

            // set a cookie before redirecting to server
            $response = FigResponseCookies::set(
                $response,
                SetCookie::create(SSOInterface::COOKIE)
                    ->withExpires(time() + (60 * 5))
                    ->withValue(time())
                    ->withPath('/')
            );

            return $response->withStatus(302)->withHeader('Location', $redirect);
        }
    }
}
