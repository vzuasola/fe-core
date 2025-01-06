<?php

namespace App\Plugins\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestMiddlewareInterface
{
    /**
     *
     */
    public function boot(RequestInterface &$request);

    /**
     *
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response);
}
