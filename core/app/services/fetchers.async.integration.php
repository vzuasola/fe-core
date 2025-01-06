<?php

// Session fetcher plugin
$container['session_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\SessionFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Balance fetcher
$container['balance_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\BalanceFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Payment account fetcher
$container['payment_account_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\PaymentAccountFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Receive News
$container['receive_news_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\ReceiveNewsFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// SMS Verification
$container['sms_verification_aysnc'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\SmsVerificationFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// User fetcher plugin
$container['user_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\UserFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Game provider fetcher
$container['game_provider_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.game_provider.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\GameProviderFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Favorite Games fetcher
$container['favorite_games_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.user_data.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\FavoriteGamesFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Recent Games fetcher
$container['recent_games_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.user_data.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\RecentGamesFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Jackpot fetcher
$container['jackpot_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.user_data.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\JackpotFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Payment fetcher
$container['payment_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');
    $lang = $c->get('lang');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = new \GuzzleHttp\Client([
        'headers' => [
            'Product' => $settings['product'],
            'IP' => \App\Utils\IP::getIpAddress(),
            'Language' => $lang
        ],
    ]);

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\PaymentFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Preference fetcher
$container['preferences_fetcher_async'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.user_data.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\AsyncIntegration\PreferencesFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};
