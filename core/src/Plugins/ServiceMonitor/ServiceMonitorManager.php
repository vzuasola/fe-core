<?php

namespace App\Plugins\ServiceMonitor;

class ServiceMonitorManager
{
    private $services = [];

    /**
     *
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     *
     */
    public function setService($id, $class)
    {
        if (method_exists($class, 'create')) {
            $instance = $class::create($this->container);
        } else {
            $instance = new $class();
        }

        $this->doSetService($id, $instance);
    }

    /**
     *
     */
    private function doSetService($id, ServiceMonitorInterface $service)
    {
        $this->services[$id] = $service;
    }

    /**
     *
     */
    public function getStatuses()
    {
        $statuses = [];

        foreach ($this->services as $key => $service) {
            $statuses[$key] = $service->check();
        }

        return $statuses;
    }
}
