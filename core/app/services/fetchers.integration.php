<?php

/**
 * Logic Layer Integration Dependencies
 *
 * Define dependencies here for logic layer integration service
 *
 */

// Base client for integration fetchers
$container['integration_client'] = new \App\Dependencies\Client\IntegrationClient();

// Session fetcher plugin
$container['session_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\SessionFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// User fetcher plugin
$container['user_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\UserFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Payment account fetcher
$container['payment_account_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\PaymentAccountFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Change password fetcher
$container['change_password'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\ChangePasswordFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// SMS Verification
$container['sms_verification'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\SmsVerificationFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// SMS
$container['sms_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\SmsFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Receive News
$container['receive_news'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\ReceiveNewsFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Balance fetcher
$container['balance_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\BalanceFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Game provider fetcher
$container['game_provider_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.game_provider.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\GameProviderFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Jackpot fetcher
$container['jackpot_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.jackpot.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\JackpotFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Jackpot Provider fetcher
$container['jackpot_provider_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.jackpot_provider.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\JackpotProviderFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Payment fetcher
$container['payment_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\PaymentFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Preference fetcher
$container['preferences_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.user_data.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\PreferencesFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Favorite Games fetcher
$container['favorites_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.user_data.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\FavoriteGamesFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};


// Recent Games fetcher
$container['recents_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.user_data.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\RecentGamesFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Two Factor Authentication Fetcher
$container['two_factor_auth_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.integration.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\TwoFactorAuthFetcher(
        $c->get('session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

$container['cookie_service'] = function ($c) {
    return \App\Integration\CookieService\CookieService::create($c);
};

$container['cookie_session'] = function ($c) {
    return \App\Player\CookieSession::create($c);
};

// PlayerGame fetcher
$container['player_game_fetcher'] = function ($c) {
    $parameters = $c->get('parameters');
    $settings = $c->get('settings');

    $host = $parameters['logic.api.url'];
    $prefix = $parameters['logic.api.game_provider.prefix'];
    $product = $settings['product'];

    $client = $c->get('integration_client');

    $prefix = "$host$prefix";

    return new \App\Fetcher\Integration\PlayerGameFetcher(
        $c->get('session'),
        $c->get('player_session'),
        $client,
        $prefix,
        $c->get('logger'),
        $product
    );
};

// Google Storage fetcher
$container['google_storage_fetcher'] = function ($c) {
    return \App\Fetcher\Integration\GoogleStorageFetcher::create($c);
};
$container['jira_service'] = function ($c) {
    return \App\Fetcher\Integration\JIRAFetcher::create($c);
};
