<?php

/**
 * Cache Dependencies
 *
 * Dependencies for the cache adapters
 *
 */

 // Cache component
$container['cache'] = function ($c) {
    return new \Symfony\Component\Cache\Adapter\FilesystemAdapter();
};

// Cache component used by system
$container['system_cache'] = new \App\Dependencies\SystemCache();

// Special cache adapter used by Page Cache
$container['page_cache_adapter'] = function ($c) {
    $settings = $c->get('settings');
    $adapter = $c->get('cache_signature');

    return $adapter(
        $c->get('system_cache'),
        $settings['page_cache']['default_timeout']
    );
};

// cache signature adapter
$container['cache_signature'] = function ($c) {
    return function ($adapter, $timeout = null) use ($c) {
        $settings = $c->get('settings');

        if (empty($settings['cache_signature']['enable'])) {
            return $adapter;
        }

        $product = $settings['product'];
        $timeout = $timeout ?? $settings['cache']['default_timeout'];

        $client = new \Predis\Client(
            $settings['cache_signature']['redis_options']['clients'],
            $settings['cache_signature']['redis_options']['options']
        );

        return new \App\Cache\Adapter\RedisSignatureAdapter(
            $adapter,
            $client,
            $product,
            $timeout
        );
    };
};

// Special cache adapter used by Front End Caches
$container['redis_cache_adapter'] = function ($c) {
    $settings = $c->get('settings');
    $product = $settings['product'];

    $options = $settings['cache']['handler_options']['options'];
    $options['prefix'] = "cache:front:$product:cache:";

    $client = new \Predis\Client(
        $settings['cache']['handler_options']['clients'],
        $options
    );

    $adapter = $c->get('cache_signature');

    return $adapter(new \Symfony\Component\Cache\Adapter\RedisAdapter($client));
};

$container['apcu_cache_adapter'] = function ($c) {
    $settings = $c->get('settings');

    if (function_exists('apcu_fetch')) {
        $handler = new \Symfony\Component\Cache\Adapter\ApcuAdapter();
    } else {
        $handler = new \Symfony\Component\Cache\Adapter\ArrayAdapter();
    }

    $adapter = $c->get('cache_signature');

    return $adapter($handler);
};
