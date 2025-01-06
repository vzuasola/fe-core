<?php

namespace App\Async;

use App\Async\Processor\Processor;

class Async
{
    private static $processor;

    /**
     * Gets the async processor
     */
    public static function processor()
    {
        if (!isset(self::$processor)) {
            self::$processor = new Processor();
        }

        return self::$processor;
    }

    /**
     * Convert definition to actual data
     *
     * @param array $definition A collection of definition array
     *
     * @return array
     */
    public static function resolve($definitions)
    {
        $processor = self::processor();

        \App\Kernel::profiler()->start(__METHOD__);

        $processed = $processor->processDefinitions($definitions);

        $time = \App\Kernel::profiler()->stop(__METHOD__);
        \App\Kernel::profiler()->setMessage("<strong>Async Time</strong>: $time ms", 0, true);

        return $processed;
    }
}
