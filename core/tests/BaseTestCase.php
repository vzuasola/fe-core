<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

use Tests\Helpers\Framework\EnvironmentTrait;
use Tests\Helpers\Request\RequestTrait;
use Tests\Helpers\Response\ResponseTrait;

class BaseTestCase extends TestCase
{
    use EnvironmentTrait;
    use RequestTrait;
    use ResponseTrait;
}
