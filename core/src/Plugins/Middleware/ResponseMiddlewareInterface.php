<?php

namespace App\Plugins\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseMiddlewareInterface
{
    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response);
}
