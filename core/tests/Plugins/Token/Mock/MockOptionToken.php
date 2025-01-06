<?php

namespace Tests\Plugins\Token\Mock;

use App\Plugins\Token\TokenInterface;

class MockOptionToken implements TokenInterface
{
    /**
     *
     */
    public function getToken($options)
    {
        return $options['name'];
    }
}
