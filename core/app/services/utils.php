<?php

/**
 * Utility Dependencies
 *
 */

// Profiler
$container['profiler'] = function ($c) {
    return new \App\Profiler\Profiler(
        $c->get('session'),
        $c->get('settings')
    );
};

// Utility methods for nodes
$container['node_utils'] = function ($c) {
    return new \App\Drupal\Node($c->get('player_session'));
};

// Utility methods for nodes
$container['block_utils'] = function ($c) {
    return new \App\Drupal\Block($c->get('router_request'));
};

// Current language
$container['lang'] = $container->factory(function ($c) {
    $request = $c->get('raw_request');

    return $request->getAttribute('language');
});

// Current product
$container['product'] = function ($c) {
    $request = $c->get('raw_request');

    return $request->getAttribute('product');
};

// Default product
$container['product_default'] = function ($c) {
    $settings = $c->get('settings');
    $products = $settings['product_url'];

    if (!is_array($products)) {
        return $products;
    }

    return reset($products);
};
