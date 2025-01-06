<?php

namespace Tests\Plugins\Token\Mock;

use App\Plugins\Token\TokenInterface;

class MockLazyToken implements TokenInterface
{
    /**
     *
     */
    public function getToken($options)
    {
        return [5 => 500];
    }
}
