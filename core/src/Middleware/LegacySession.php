<?php

namespace App\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Dflydev\FigCookies\Cookies;
use App\Legacy\LegacyEncryption;

/**
 * Handles legacy session handling
 */
class LegacySession
{
    /**
     * The key of the cookie to look for
     */
    const COOKIE_KEY = 'token';

    /**
     *
     */
    protected $handler;

    /**
     *
     */
    protected $playerSession;

    /**
     *
     */
    protected $logger;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->handler = $container->get('handler');
        $this->playerSession = $container->get('player_session');
        $this->logger = $container->get('logger');
    }

    /**
     *
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $token = $this->getSessionCookie($request);

        if ($token) {
            try {
                $this->playerSession->authenticateByToken($token);

                $handler = $this->handler->getEvent('event_legacy_login_success');
                return $handler($request, $response, $token);
            } catch (\Exception $e) {
                // logging that there is an invalid token

                $handler = $this->handler->getEvent('event_legacy_login_failed');
                return $handler($request, $response, $token);
            }
        }

        $response = $next($request, $response);

        return $response;
    }

    /**
     * Get the session cookie value from the request object
     *
     * @return string
     */
    private function getSessionCookie(RequestInterface $request)
    {
        $cookies = Cookies::fromRequest($request);

        $cookie = $cookies->get(LegacySession::COOKIE_KEY);

        if ($cookie) {
            $value = $cookie->getValue();

            return LegacyEncryption::decrypt($value);
        }
    }
}
