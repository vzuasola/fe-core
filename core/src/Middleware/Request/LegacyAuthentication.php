<?php

namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use Dflydev\FigCookies\Cookies;

use App\Legacy\LegacyEncryption;
use App\Middleware\Cache\ResponseCache;
use App\Plugins\Middleware\RequestMiddlewareInterface;
use App\Plugins\Middleware\TerminateException;

/**
 * Handles legacy session handling
 */
class LegacyAuthentication implements RequestMiddlewareInterface
{
    /**
     * The key of the query params to look for
     */
    const PARAM_KEY = 'token';

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
    public function boot(RequestInterface &$request)
    {
        $token = $this->getSessionQuery($request);

        // don't run page cache if I have a query parameter
        if ($token) {
            $request = $request->withAttribute(ResponseCache::CACHE_SKIP, true);
        }
    }

    /**
     *
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        $token = $this->getSessionQuery($request);

        if ($token) {
            try {
                $this->playerSession->authenticateByToken($token);

                $handler = $this->handler->getEvent('legacy_login_success');
                $response = $handler($request, $response, $token);

                throw new TerminateException($request, $response);
            } catch (\Exception $e) {
                $handler = $this->handler->getEvent('legacy_login_failed');
                $response = $handler($request, $response, $token);

                throw new TerminateException($request, $response);
            }
        }
    }

    /**
     * Get the session query value from the request object
     *
     * @return string
     */
    private function getSessionQuery(RequestInterface $request)
    {
        $params = $request->getQueryParams();

        if (isset($params[self::PARAM_KEY])) {
            $token = $params[self::PARAM_KEY];

            return LegacyEncryption::decrypt($token);
        }
    }
}
