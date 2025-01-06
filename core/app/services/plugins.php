<?php

/**
 * Plugin Managers
 *
 * Define dependencies that handles plugins
 *
 */

// Route manager
$container['route_manager'] = function ($c) {
    return new \App\Plugins\Route\RouteManager($c);
};

// Route manager
$container['middleware_manager'] = function ($c) {
    return new \App\Plugins\Middleware\MiddlewareManager($c);
};

// Event Handler Manager
$container['handler'] = function ($c) {
    return new \App\Plugins\Handler\HandlerManager($c);
};

// Form Manager
$container['form_manager'] = function ($c) {
    return new \App\Plugins\Form\FormManager($c);
};

// Form Builder
$container['form_builder_factory'] = function ($c) {
    return new \App\Plugins\Form\Builder\Factory(
        $c->get('scripts'),
        $c->get('configuration_manager')
    );
};

// System Configuration Manager
$container['configuration_manager'] = function ($c) {
    return new \App\Configuration\YamlConfiguration(
        $c->get('settings')['configurations']
    );
};

// Translation Manager
$container['translation_manager'] = function ($c) {
    return new \App\Plugins\Translation\TranslationManager(
        $c->get('lang'),
        $c->get('language_fetcher')
    );
};

// Section Manager
$container['section_manager'] = function ($c) {
    return new \App\Plugins\Section\SectionManager($c);
};

// Token manager
$container['token_manager'] = function ($c) {
    return new \App\Plugins\Token\TokenManager($c);
};

// Token Parser
$container['token_parser'] = function ($c) {
    $tokens = new \App\Plugins\Token\Parser(
        $c->get('token_manager')
    );

    $tokens->setParser(
        new \App\Plugins\Token\Parser\QueryParser()
    );

    $tokens->setParser(
        new \App\Plugins\Token\Parser\UriParser(
            $c->get('asset')
        )
    );

    return $tokens;
};

// Game provider manager
$container['game_provider_manager'] = function ($c) {
    return new \App\Plugins\GameProvider\GameProviderManager($c);
};

// Script provider manager
$container['script_provider_manager'] = function ($c) {
    return new \App\Plugins\Javascript\ScriptManager($c);
};

// Menu Widget Manager
$container['menu_widget_manager'] = function ($c) {
    return new \App\Plugins\Widget\MenuWidgetManager($c);
};

// Component Widget Manager
$container['component_widget_manager'] = function ($c) {
    return new \App\Plugins\ComponentWidget\ComponentWidgetManager($c);
};

// Component Widget Manager
$container['widgets'] = function ($c) {
    return \App\Plugins\ComponentWidget\ComponentWidgetRenderer::create($c);
};

// Service Monitor Manager
$container['service_monitor_manager'] = function ($c) {
    return new \App\Plugins\ServiceMonitor\ServiceMonitorManager($c);
};

// Secure Session
$container['secure_session'] = function ($c) {
    return new \App\Session\SecureSession($c->get('session'));
};
