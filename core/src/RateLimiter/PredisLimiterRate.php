<?php

namespace App\RateLimiter;

class PredisLimiterRate
{
    private $interval;

    private $maxActions;

    public  function __construct(int $intervalInSec, int $actionPerInterval)
    {
        $this->interval = $intervalInSec;
        $this->maxActions = $actionPerInterval;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function getMaxActions()
    {
        return $this->maxActions;
    }
}
