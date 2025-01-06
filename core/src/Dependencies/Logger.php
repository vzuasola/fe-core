<?php

namespace App\Dependencies;

/**
 * Dynamic monolog logger object with channels support
 */
class Logger
{
    /**
     * The service container
     */
    private $container;

    /**
     * Store channel instances
     */
    private $channels;

    /**
     *
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->settings = $container->get('settings');
    }

    /**
     *
     */
    public function __invoke($index = null)
    {
        if ($index) {
            return $this->getChannelInstance($index);
        }

        return $this->getChannelInstance('default');
    }

    /**
     *
     */
    public function __call($method, $arguments)
    {
        // flag to disable the logging
        if (isset($this->settings['logger']['disable']) &&
            $this->settings['logger']['disable']
        ) {
            return;
        }

        return $this->getChannelInstance('default')->$method(...$arguments);
    }

    /**
     *
     */
    private function getChannelInstance($channel)
    {
        if ($channel === 'default') {
            $class = $this->settings['logger']['default_channel'];
        } else {
            $class = $this->settings['logger']['channels'][$channel];
        }

        if (!isset($this->channels[$channel])) {
            $invoke = new $class();

            $instance = $invoke($this->container);
            $this->channels[$channel] = $instance;
        } else {
            $instance = $this->channels[$channel];
        }

        return $instance;
    }
}
