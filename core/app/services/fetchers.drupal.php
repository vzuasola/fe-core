<?php

/**
 * Logic Layer Drupal Dependencies
 *
 * Define dependencies here for logic layer drupal service
 *
 */

// Base client for Drupal fetchers
$container['drupal_client'] = new \App\Dependencies\Client\DrupalClient();

// Drupal node fetcher plugin
$container['node_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\NodeFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Drupal menu fetcher plugin
$container['menu_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\MenuFetcher(
        $client,
        $prefix,
        $c->get('router_request'),
        $c->get('lang'),
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Drupal form builder plugin
$container['form_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\FormFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache'),
        $c->get('form_builder_factory'),
        $c->get('scripts'),
        $c->get('configuration_manager')
    );
};

// Drupal form builder plugin
$container['config_form_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\ConfigFormFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache'),
        $c->get('form_builder_factory')
    );
};

// Drupal configuration fetcher plugin
$container['config_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\ConfigFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Drupal configuration fetcher plugin
$container['territory_blocking_fetcher'] = function ($c) {

    return new \App\Fetcher\Drupal\TerritoryBlockingFetcher(
        $c->get('config_fetcher')
    );
};

// Drupal block fetcher plugin
$container['block_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\BlockFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Drupal language fetcher plugin
$container['language_fetcher'] = function ($c) {
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

    return new \App\Fetcher\Drupal\LanguageFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Drupal view fetcher plugin
$container['views_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\ViewsFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Drupal Domains fetcher plugin
$container['domain_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\DomainFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache'),
        $c->get('config_fetcher')
    );
};

// Drupal snippet fetcher plugin
$container['snippet_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\SnippetFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Drupal product fetcher plugin
$container['product_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\ProductFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// File fetcher
$container['file_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\FileFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};

// Mail fetcher
$container['mail_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.drupal.prefix'];
    $product = $settings['product'];

    $client = $c->get('drupal_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Drupal\MailFetcher(
        $client,
        $prefix,
        $c->get('logger'),
        $product,
        $c->get('fetcher_cache')
    );
};
