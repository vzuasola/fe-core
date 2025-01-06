<?php

namespace Tests\Plugins\Token\Mock;

use App\Plugins\Token\TokenInterface;

class MockAgeToken implements TokenInterface
{
    /**
     *
     */
    public function getToken($options)
    {
        return 35;
    }
}
