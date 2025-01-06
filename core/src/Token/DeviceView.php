<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 *
 */
class DeviceView implements TokenInterface
{
    const HEADER = 'X-Custom-Device-View';

    /**
     * Request object
     *
     * @var object
     */
    private $request;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->request = $container->get('router_request');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        return $this->request->getHeaderLine(self::HEADER) ?: 'desktop';
    }
}
