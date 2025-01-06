<?php

namespace App;

use Interop\Container\ContainerInterface;

/**
 * Controller Resolver
 */
class Resolver implements \ArrayAccess
{
    /**
     * Public constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Forward call to a container
     */
    public function forward($controller)
    {
        return new $controller($this->container);
    }

    /**
     * Gets a parameter or an object.
     */
    public function offsetGet($id)
    {
        return $this->forward($id);
    }

    /**
     * Checks if a parameter or an object is set.
     */
    public function offsetExists($id)
    {
        return class_exists($id);
    }

    /**
     * Sets a parameter or an object.
     */
    public function offsetSet($id, $value)
    {
    }

    /**
     * Unsets a parameter or an object.
     */
    public function offsetUnset($id)
    {
    }
}
