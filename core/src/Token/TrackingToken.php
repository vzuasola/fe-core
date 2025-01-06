<?php

namespace App\Token;

use Interop\Container\ContainerInterface;

use App\Plugins\Token\TokenInterface;
use App\Middleware\Affiliates;
use App\Cookies\Cookies;

/**
 * Exposes the token that appends query string for legacy post login
 */
class TrackingToken implements TokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function getToken($options)
    {
        return Cookies::get('affiliates');
    }
}
