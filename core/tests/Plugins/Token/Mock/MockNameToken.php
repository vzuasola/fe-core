<?php

namespace Tests\Plugins\Token\Mock;

use App\Plugins\Token\TokenInterface;

class MockNameToken implements TokenInterface
{
    /**
     *
     */
    public function getToken($options)
    {
        return 'leandrew';
    }
}
