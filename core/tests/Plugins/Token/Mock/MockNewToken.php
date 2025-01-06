<?php

namespace Tests\Plugins\Token\Mock;

use App\Plugins\Token\TokenInterface;

class MockNewToken implements TokenInterface
{
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     *
     */
    public function getToken($options)
    {
        return 'Lorem ipsum dolor';
    }
}
