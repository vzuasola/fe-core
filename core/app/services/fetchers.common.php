<?php

/**
 * Logic Layer Common API Dependencies
 *
 * Define dependencies here for logic layer common API service
 *
 */

// Common API Base Client
$container['common_client'] = new \App\Dependencies\Client\CommonClient($container);

// Common API fetcher
$container['common_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('common_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncDrupal\CommonFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};
