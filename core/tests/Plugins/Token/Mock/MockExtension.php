<?php

namespace Tests\Plugins\Token\Mock;

use App\Plugins\Token\TokenExtensionInterface;

class MockExtension implements TokenExtensionInterface
{
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function process(&$tokens)
    {
        $tokens['new'] = MockNewToken::class;
    }
}
