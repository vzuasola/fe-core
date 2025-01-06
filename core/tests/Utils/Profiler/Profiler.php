<?php

namespace Tests\Utils\Profiler;

use App\Profiler\Profiler as Debug;
use App\Session\SessionHandler;
use Tests\Helpers\Request\RequestTrait;
use Tests\Utils\Monolog\Logger;

class Profiler
{
    public static function createInstance()
    {
        $class = new static();

        return $class->getInstance();
    }

    public function __construct()
    {
        $session = new SessionHandler('webcomposer', 'webcomposer.com', new Logger());

        $this->profiler = new Debug($session, [
            'debug' => 0
        ]);
    }

    public function getInstance()
    {
        return $this->profiler;
    }
}
