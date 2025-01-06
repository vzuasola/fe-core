<?php

namespace App\Plugins\ServiceMonitor;

interface ServiceMonitorInterface
{
    /**
     * Check the status of a service if it is valid or not
     *
     * @return boolean
     */
    public function check();
}
