<?php

/**
 * System Dependencies
 *
 * Dependencies that makes the framework work
 *
 */

// Monolog
$container['logger'] = function ($c) {
    return new \App\Dependencies\Logger($c);
};

// Twig
$container['view'] = new \App\Dependencies\Twig();

// Raw request
$container['raw_request'] = new \App\Dependencies\Request($container);

$container['client_stats'] = function ($c) {
    return new \App\Fetcher\ClientStats($c->get('settings'));
};

// Session component
$container['session'] = function ($c) {
    return \App\Session\SessionHandler::create($c);
};

// HTTP caching
$container['http_cache'] = function ($c) {
    return new \Slim\HttpCache\CacheProvider();
};

// Controller Resolver
$container['resolver'] = function ($c) {
    return new \App\Resolver($c);
};

// Javascript attachments
$container['scripts'] = function ($c) {
    return new \App\Plugins\Javascript\Settings(
        $c->get('script_provider_manager')
    );
};

// Rest resource
$container['rest'] = function ($c) {
    return new \App\Rest\Resource($c->get('router_request'));
};

// URI manager
$container['uri'] = function ($c) {
    $uri = new \App\Url\Url(
        $c->get('raw_request'),
        $c->get('lang'),
        $c->get('product'),
        $c->get('language_fetcher'),
        $c->get('settings')
    );

    $uri->setParser(
        new \App\Url\Parser\TokenParser(
            $c->get('token_parser')
        )
    );

    $uri->setParser(
        new \App\Url\Parser\QueryParser()
    );

    return $uri;
};

// Asset manager
$container['asset'] = function ($c) {
    $manifest = $c->get('asset_manifest');

    return new \App\Url\Asset(
        $c->get('request'),
        $c->get('lang'),
        $c->get('product'),
        $c->get('settings'),
        $c->get('config_fetcher'),
        $manifest
    );
};

// Asset manifest
$container['asset_manifest'] = function ($c) {
    try {
        $cwd = getcwd();
        $manifest = @file_get_contents("$cwd/manifest.json");
        $manifest = json_decode($manifest, true);
    } catch (\Exception $e) {
        $manifest = false;
    }

    return $manifest;
};

// Fetcher cache
$container['fetcher_cache'] = function ($c) {
    return \App\Fetcher\Cache::create($c);
};
