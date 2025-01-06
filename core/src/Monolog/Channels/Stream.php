<?php

namespace App\Monolog\Channels;

use App\Monolog\Processors\LogRequirementsProcessor;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

/**
 * Default monolog logger instance
 */
class Stream
{
    /**
     *
     */
    public function __invoke($container)
    {
        $settings = $container->get('settings')['logger'];

        $logger = new Logger($settings['name']);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new LogRequirementsProcessor());

        $format = new JsonFormatter();
        $stream = new StreamHandler($settings['path'], $settings['level']);
        $stream->setFormatter($format);

        $logger->pushHandler($stream);

        return $logger;
    }
}
