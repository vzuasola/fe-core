<?php

// Player Session
$container['player_session'] = function ($c) {
    return new \App\Player\PlayerSession(
        $c->get('session'),
        $c->get('secure_session'),
        $c->get('session_fetcher'),
        $c->get('user_fetcher'),
        $c->get('handler'),
        $c->get('cookie_session')
    );
};

$container['player'] = function ($c) {
    return \App\Player\Player::create($c);
};

// Legacy Auth
$container['legacy_authentication'] = function ($c) {
    return new \App\Legacy\LegacyAuthentication(
        $c->get('player_session'),
        $c->get('session')
    );
};

// Session Storage SSO
$container['session_sso'] = function ($c) {
    return new \App\SSO\SessionStorageSSO(
        $c->get('player_session'),
        \App\Utils\LazyService::createLazyDependency($c, 'config_fetcher'),
        $c->get('request'),
        $c->get('jwt_encryption'),
        $c->get('logger')
    );
};

// Encryption libraries
$container['legacy_encryption'] = function ($c) {
    return new \App\Encryption\LegacyEncryption();
};

// JWT encryption library
$container['jwt_encryption'] = function ($c) {
    return new \App\Encryption\JWTEncryption();
};

// Get player VIP Level
$container['player_details'] = function ($c) {
    return \App\Player\PlayerDetails::create($c);
};

// Email submit
$container['mail_submit'] = function ($c) {
    return new \App\Fetcher\Drupal\MailFetcher(
        $c->get('mail_fetcher')
    );
};

// Localized Language
$container['localization'] = function ($c) {
    return new \App\Localization\Localization(
        $c->get('raw_request'),
        $c->get('configuration_manager'),
        $c->get('settings'),
        $c->get('lang'),
        $c->get('player_session'),
        $c->get('player')
    );
};

// Balance service
$container['balance'] = function ($c) {

    $dcoinEnabled = \App\Utils\DCoin::isDafacoinEnabled(
        $c->get('config_fetcher')->getGeneralConfigById('header_configuration'),
        $c->get('player_session')
    );

    return new \App\Player\Balance\Balance(
        $c->get('balance_fetcher_async'),
        $dcoinEnabled
    );
};

$container['predis_rate_limiter'] = function ($c) {

    $settings = $c->get('settings');
    $product = $settings['product'];

    $options = $settings['cache']['handler_options']['options'];
    $options['prefix'] = "cache:front:$product:cache:";

    $predisClient = new \Predis\Client(
        $settings['cache']['handler_options']['clients'],
        $options
    );

    $rateLimiter = new \App\RateLimiter\PredisRateLimiter(
        $predisClient,
        $c->get('user_fetcher')
    );

    return $rateLimiter;
};

$container['sms_blocker'] = function ($c) {
    return new \App\SMS\Blocker(
        $c->get('config_fetcher'),
        $c->get('user_fetcher')
    );
};
