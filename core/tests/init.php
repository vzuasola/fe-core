<?php

$profiler = Tests\Utils\Profiler\Profiler::createInstance();
$logger = new \Tests\Utils\Monolog\Logger();

$container = \Tests\MockContainer::createInstance();
$container->set('profiler', $profiler);
$container->set('logger', $logger);

\App\Kernel::setContainer($container);
