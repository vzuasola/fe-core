<?php

namespace App\Monolog\Processors;

/**
 * Monolog processor for logging
 */
class LogRequirementsProcessor
{
    public function __invoke(array $record)
    {
        // Add timestamp on context
        if (!isset($record['context']['timestamp'])) {
            $record['context']['timestamp'] = "";

            if (isset($record['datetime'])) {
                $record['context']['timestamp'] = $record['datetime']->format('Y-m-d H:i:s.v');
            }
        }

        // Add log level on context if not exist
        if (!isset($record['context']['log_level']) && isset($record['level_name'])) {
            $record['context']['log_level'] = $record['level_name'];
        }

        return $record;
    }
}
