<?php

// Drupal menu fetcher async plugin
$container['menu_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\MenuFetcher(
        $client,
        $prefix,
        $c->get('router_request'),
        $c->get('lang'),
        $c->get('logger'),
        $product
    );
};

// Drupal Domains fetcher plugin
$container['domain_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\DomainFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('config_fetcher_async')
    );
};

// Drupal form fetcher plugin
$container['webform_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\WebformFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('form_builder_factory'),
        $c->get('scripts'),
        $c->get('configuration_manager')
    );
};

// Drupal configuration fetcher plugin
$container['config_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\ConfigFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Drupal language fetcher plugin
$container['language_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = new \GuzzleHttp\Client([
        'headers' => [
            'Product' => $settings['product'],
        ],
    ]);

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\LanguageFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Drupal node fetcher plugin
$container['node_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\NodeFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Drupal product fetcher plugin
$container['product_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\ProductFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Drupal snippet fetcher plugin
//
// Not to be migrated as of now
//
// $container['snippet_fetcher_async'] = function ($c) {
//     $parameters = $c->get('parameters');
//     $settings = $c->get('settings');

//     $host = $parameters['logic.api.url'];
//     $prefix = $parameters['logic.api.drupal.prefix'];
//     $product = $settings['product'];

//     $client = $c->get('drupal_client');

//     $prefix = "$host$prefix";

//     return new \App\Fetcher\AsyncDrupal\SnippetFetcher(
//         $client,
//         $prefix,
//         $c->get('logger'),
//         $product
//     );
// };

// Drupal Geolocation Fetcher plugin
$container['geolocation_fetcher'] = function ($c) {
    return new \App\Fetcher\AsyncDrupal\GeolocationFetcher(
        $c->get('config_fetcher_async')
    );
};

// Drupal async view fetcher plugin
$container['views_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\ViewsFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};
