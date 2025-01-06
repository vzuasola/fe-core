<?php

namespace App\Monolog\Channels;

use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

use App\Monolog\Processors\WorkflowProcessor;
use App\Monolog\Formatter\JsonFormatter;

/**
 * Default monolog logger instance
 */
class Workflow
{
    /**
     *
     */
    public function __invoke($container)
    {
        $settings = $container->get('settings')['metrics_log'];

        $logger = new Logger($settings['name']);
        $logger->pushProcessor(new WorkflowProcessor($container));

        $format = new JsonFormatter();
        $stream = new StreamHandler($settings['path'], $settings['level']);
        $stream->setFormatter($format);

        $logger->pushHandler($stream);

        return $logger;
    }
}
