<?php

use App\Utils\Url;

// Put handlers here

$container['event_login_success'] = function ($c) {
    return function ($request, $response, $username, $password, $destination, $isAjax = false) use ($c) {
        $handler = $c->get('handler')->getEvent('login_success_redirection');
        return $handler($request, $response, $destination);
    };
};

$container['event_login_success_redirection'] = function ($c) {
    return function ($request, $response, $destination) use ($c) {
        try {
            $geoIp = $request->getHeaderLine('x-custom-lb-geoip-country') ?? null;
            if ($geoIp && $c->player_session->isLogin()) {
                $geoIp = strtolower($geoIp);
                $key = 'dafabet.language.popup.geoip';
                $existingData = $c->preferences_fetcher->getPreferences()[$key] ?? [];

                if ($existingData && in_array($geoIp, $existingData)) {
                    $playerLanguageMapping = $c->configuration_manager->getConfiguration('player-language');
                    $languages = $playerLanguageMapping['player-language'];
                    $playerLanguage = $c->player->getLocale();
                    $langPrefix = $languages[$playerLanguage];
                    $destination = str_replace($c->lang, $langPrefix, $destination);
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }
        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

$container['event_login_failed'] = function ($c) {
    return function ($request, $response, $username, $destination, $isAjax = false) use ($c) {
        try {
            $config = $c->get('config_fetcher')->getGeneralConfigById('login_configuration');
            $message = $config['error_message_invalid_passname'];
        } catch (\Exception $e) {
            $message = 'Sorry the Username and Password is invalid.';
        }

        $c->session->setFlash('login.error', $message);
        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

$container['event_logout'] = function ($c) {
    return function ($request, $response) use ($c) {
        $destination = $request->getQueryParam('from', '/');

        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

$container['event_account_locked'] = function ($c) {
    return function ($request, $response, $username, $destination, $isAjax = false) use ($c) {
        try {
            $config = $c->get('config_fetcher')->getGeneralConfigById('login_configuration');
            $message = $config['error_message_account_locked'];
        } catch (\Exception $e) {
            $message = 'Sorry this Username is locked.';
        }

        $c->session->setFlash('login.error', $message);
        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

$container['event_account_suspended'] = function ($c) {
    return function ($request, $response, $username, $destination, $isAjax = false) use ($c) {
        try {
            $config = $c->get('config_fetcher')->getGeneralConfigById('login_configuration');
            $message = $config['error_message_account_suspended'];
        } catch (\Exception $e) {
            $message = 'Sorry this Username is suspended.';
        }

        $c->session->setFlash('login.error', $message);
        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

$container['event_session_invalid'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $response;
    };
};

$container['event_service_not_available'] = function ($c) {
    return function ($request, $response, $username, $destination, $isAjax = false) use ($c) {
        try {
            $config = $c->get('config_fetcher')->getGeneralConfigById('login_configuration');
            $message = $config['error_message_service_not_available'];
        } catch (\Exception $e) {
            $message = 'Service not available.';
        }

        $c->session->setFlash('login.error', $message);
        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

// Legacy Session Handler

$container['event_legacy_login_success'] = function ($c) {
    return function ($request, $response, $token) use ($c) {
        $destination = Url::generateFromRequest($request, $request->getUri()->getPath());

        // remove only token from the query parameters to preserve other
        // queries
        $params = $request->getQueryParams();
        unset($params['token']);

        $query = http_build_query($params);

        if (!empty($query)) {
            $destination = "$destination?$query";
        }

        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

$container['event_legacy_login_failed'] = function ($c) {
    return function ($request, $response) use ($c) {
        $destination = Url::generateFromRequest($request, $request->getUri()->getPath());

        // remove only token from the query parameters to preserve other
        // queries
        $params = $request->getQueryParams();
        unset($params['token']);

        $query = http_build_query($params);

        if (!empty($query)) {
            $destination = "$destination?$query";
        }

        return $response->withStatus(302)->withHeader('Location', $destination);
    };
};

$container['event_unsupported_currency'] = function ($c) {
    return function ($request, $response) use ($c) {
        $response = $response->withStatus(403);
        return $c['resolver']['App\Controller\AccessController']
            ->unsupportedCurrency($request, $response);
    };
};

$container['event_site_maintenance'] = function ($c) {
    return function ($request, $response) use ($c) {
        $response = $response->withStatus(200);
        return $c['resolver']['App\Controller\MaintenancePageController']
            ->getMaintenanceConfig($request, $response);
    };
};
