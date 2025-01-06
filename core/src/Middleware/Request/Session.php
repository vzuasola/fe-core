<?php

namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

use App\Cookies\Cookies;
use App\Plugins\Middleware\RequestMiddlewareInterface;

/**
 * Handles early session requests
 */
class Session implements RequestMiddlewareInterface
{
    /**
     *
     */
    protected $handler;

    /**
     *
     */
    protected $session;

    /**
     *
     */
    protected $playerSession;

    /**
     *
     */
    protected $timeout;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->handler = $container->get('handler');
        $this->scripts = $container->get('scripts');
        $this->session = $container->get('session');
        $this->playerSession = $container->get('player_session');
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
        $login = $this->session->get('login');

        try {
            $valid = $this->playerSession->isLogin();
        } catch (\Exception $e) {
            $valid = false;
        }

        if ($login && $valid) {
            $this->scripts->attach([
                'login' => true,
            ]);

            // store the username to the cookie to be used by any
            // 3rd party scripts e.g. GA
            $username = $this->playerSession->getUsername();

            Cookies::set('username', $username, [
                'path' => '/'
            ]);
        }

        // if the session is not valid, call the invalid session handler on the
        // service container
        if ($login && !$valid) {
            $this->session->delete('login');
            $this->session->delete('token');
            $this->session->delete('username');

            $event = $this->handler->getEvent('session_invalid');
            $response = $event($request, $response);
        }

        if (!$login) {
            $this->handler->broadcast('session_cleanup', []);

            Cookies::remove('username', [
                'path' => '/'
            ]);
        }
    }
}
