<?php

namespace App\Middleware\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use App\Plugins\Middleware\ResponseMiddlewareInterface;

use App\Cookies\Cookies;

/**
 * Handles last visited product cookie handling
 */
class LastVisitedProductCookie implements ResponseMiddlewareInterface
{
    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {

        $product = $response->getAttribute('product');

        $visitedProduct = Cookies::get('last_visited_product');

        if (empty($visitedProduct) || $visitedProduct != $product) {
            $isXhr = $request->isXhr();
            $status = $response->getStatusCode();
            $currentPath = $request->getUri()->getPath();
            $gamesApi = strpos($currentPath, '/api/games');

            if ($status === 200 && !$isXhr && $gamesApi !== 0) {
                Cookies::set('last_visited_product', $product, [
                    'expire' => time() + (60 * 60 * 24 * 30),
                    'http' => false,
                    'path' => '/',
                ]);
            }
        }
    }
}
