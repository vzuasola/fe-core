<?php

namespace App\Fetcher;

use GuzzleHttp\TransferStats;

class ClientStats
{
    private $stack = [];

    /**
     *
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Push a record to the stack
     *
     * @param TransferStats $stats
     */
    public function record(TransferStats $stats)
    {
        $stash['stats'] = $stats;

        if ($this->settings->get('debug')) {
            $stash['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        $this->stack[] = $stash;
    }

    /**
     * Gets the recorded stack
     *
     * @return array
     */
    public function getStack()
    {
        return $this->stack;
    }
}
