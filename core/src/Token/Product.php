<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the product on the token system
 */
class Product implements TokenInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->product = $container->get('product');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        return $this->product;
    }
}
