<?php

namespace App\ServiceMonitor;

use App\Plugins\ServiceMonitor\ServiceMonitorInterface;
use Predis\Client;

class RedisServiceMonitor implements ServiceMonitorInterface
{
    private $settings;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('settings')
        );
    }

    /**
     *
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @{inheritdoc}
     */
    public function check()
    {
        $result = true;

        if ($this->settings['session_handler']['handler'] == 'predis') {
            $client = new Client(
                $this->settings['session_handler']['handler_options']['clients'],
                $this->settings['session_handler']['handler_options']['options']
            );

            try {
                $client->ping();
            } catch (\Exception $e) {
                $result = "The session handler Redis client is not accessible";
            }
        }

        if ($this->settings['cache']['handler'] == 'predis') {
            $client = new Client(
                $this->settings['cache']['handler_options']['clients'],
                $this->settings['cache']['handler_options']['options']
            );

            try {
                $client->ping();
            } catch (\Exception $e) {
                $result = "The cache handler Redis client is not accessible";
            }
        }

        return $result;
    }
}
