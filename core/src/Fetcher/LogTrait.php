<?php

namespace App\Fetcher;

use App\Fetcher\AppDynamicsDefinition;
use App\Fetcher\LogDefinition;

/**
 * Trait for easy logging of common log messages
 */
trait LogTrait
{
    /**
     * Logs the client info calls
     */
    protected function logInfo($uri, $body)
    {
        $this->logger->info('request', [
            'component' => static::class,
            'source' => $uri,
            'username' => '',
            'duration' => $this->data['duration'] ?? '',
            'country_code' => $this->data['country_code'] ?? '',
            'ip' => $this->data['ip'] ?? '',
            'cached' => $this->data['cached'] ?? '',
            'action' => 'Guzzle client request object',
            'object' => '',
            'status' => 'Successful request',
            'response' => $body,
        ]);
    }

    /**
     * Logs the client exceptions
     */
    protected function logException($title, $uri, $e)
    {
        $this->logger->error($title, [
            'component' => static::class,
            'source' => $uri,
            'username' => $this->data['username'] ?? '',
            'duration' => $this->data['duration'] ?? '',
            'country_code' => $this->data['country_code'] ?? '',
            'ip' => $this->data['ip'] ?? '',
            'cached' => $this->data['cached'] ?? '',
            'action' => @LogDefinition::FETCHERS[static::class]['failed']['action'],
            'object' => @LogDefinition::FETCHERS[static::class]['failed']['object'],
            'status' => @LogDefinition::FETCHERS[static::class]['failed']['status'],
            'response' => $e->getCode(),
            'exception' => $e->getMessage(),
        ]);
    }
}
