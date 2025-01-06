<?php

namespace Tests;

use Interop\Container\ContainerInterface;

class MockContainer implements ContainerInterface
{

    /**
     * property that will contain all possible values inside the container
     */
    public $container;

    public static function createInstance()
    {
        return new static();
    }

    /**
     * Magic method get
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * Magic method set
     */
    public function __set($id, $value)
    {
        $this->container[$id] = $value;
    }

    /**
     * Alias function to fetch services from a container
     *
     * @return mixed
     */
    public function get($id)
    {
        return (isset($this->container[$id])) ? $this->container[$id] : null;
    }

    /**
     * Alias function to fetch services from a container
     *
     * @return mixed
     */
    public function set($id, $value)
    {
        $this->container[$id] = $value;
    }

    /**
     * Alias function to fetch services from a container
     *
     * @return mixed
     */
    public function has($id)
    {
        return (boolean) $this->get($id);
    }
}
