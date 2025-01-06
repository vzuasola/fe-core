<?php

namespace App\Plugins\GameProvider;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 */
interface GameProviderInterface
{
    /**
     * Hook execute on page initialization
     */
    public function init(RequestInterface $request, ResponseInterface $response);

    /**
     * Defines how to authenticate a game provider on login
     *
     * @param string $username
     * @param string $password
     *
     * @return boolean
     */
    public function authenticate($username, $password);

    /**
     * Hook execute on session destroy
     */
    public function onSessionDestroy();

    /**
     * Get the Javascript assets for a game provider if any
     *
     * @return array
     */
    public function getJavascriptAssets();
}
