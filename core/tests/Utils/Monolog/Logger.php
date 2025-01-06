<?php

namespace Tests\Utils\Monolog;

class Logger
{
    public function __invoke()
    {
        return new static();
    }

    public function __call($method, $arguments)
    {
        return true;
    }
}
