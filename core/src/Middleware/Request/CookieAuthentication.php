<?php

namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Cookies\Cookies;
use App\Legacy\LegacyEncryption;
use App\Middleware\Cache\ResponseCache;
use App\Plugins\Middleware\RequestMiddlewareInterface;
use App\Plugins\Middleware\TerminateException;

/**
 * Handles Cookie Session
 */
class CookieAuthentication implements RequestMiddlewareInterface
{
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
     *  Cookie Session Object Class
     */
    protected $cookieSession;

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
        $this->cookieSession = $container->get('cookie_session');
    }

    /**
     *
     */
    public function boot(RequestInterface &$request)
    {
        $cookie = $this->cookieSession->read();
        try {
            // don't run page cache if there's a cookie to be read
            if (!$this->playerSession->isLogin() && $cookie) {
                $request = $request->withAttribute(ResponseCache::CACHE_SKIP, true);
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     *
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        $cookieWbc = Cookies::get('wbcToken');

        if ($cookieWbc && !empty($cookieWbc) && !$this->playerSession->isLogin()) {
            try {
                $cookie = $this->cookieSession->read();
                $this->playerSession->authenticateByToken($cookie['sessionToken']);
            } catch (\Exception $e) {
                $this->logger->info('Cookie Get for session failed - ' . $e->getMessage());
                // do nothing
            }
        }
    }
}
