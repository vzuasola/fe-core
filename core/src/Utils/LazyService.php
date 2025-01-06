<?php

namespace App\Utils;

use Interop\Container\ContainerInterface;

/**
 *
 */
class LazyService
{
    /**
     * Create a lazy service instance
     */
    public static function createLazyDependency(ContainerInterface $container, $object)
    {
        return new class($container, $object) {
            private $container;
            private $object;

            public function __construct($container, $object)
            {
                $this->container = $container;
                $this->object = $object;
            }

            public function __invoke()
            {
                return $this->container->get($this->object);
            }

            public function __call($method, $arguments)
            {
                $dependency = $this->container->get($this->object);

                return $dependency->$method(...$arguments);
            }
        };
    }

    /**
     * Create a lazy service instance
     */
    public static function create($object)
    {
        return new class($object) {
            private $object;

            public function __construct($object)
            {
                $this->object = $object;
            }

            public function __call($method, $arguments)
            {
                return $this->object->$method(...$arguments);
            }
        };
    }
}
