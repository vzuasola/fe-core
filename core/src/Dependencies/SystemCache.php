<?php

namespace App\Dependencies;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 *
 */
class SystemCache
{
    /**
     *
     */
    public function __invoke($container)
    {
        $settings = $container->get('settings');

        $handler = $settings['cache']['handler'];

        switch ($handler) {
            case 'predis':
                $cacher = $this->predisCache($settings);
                break;

            default:
                $cacher = $this->fileSystemCache($settings);
                break;
        }

        return $cacher;
    }

    /**
     *
     */
    private function predisCache($settings)
    {
        $client = new \Predis\Client(
            $settings['cache']['handler_options']['clients'],
            $settings['cache']['handler_options']['options']
        );

        return new RedisAdapter($client);
    }

    /**
     *
     */
    private function fileSystemCache($settings)
    {
        return new FilesystemAdapter(
            $settings['cache']['handler_options']['namespace'],
            $settings['cache']['handler_options']['lifetime'],
            $settings['cache']['handler_options']['path']
        );
    }
}
