<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

use App\Drupal\Config;
use App\Form\LoginForm;
use App\Form\LoginLightboxForm;
use App\Utils\Url;

use DateTime;
use DateTimeZone;

class HeaderAsync implements AsyncSectionInterface
{
    /**
     * Timestamp format
     */
    const TIMESTAMP_FORMAT = 'Y/m/d H:i:s';

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->menu = $container->get('menu_fetcher_async');
        $this->config = $container->get('config_fetcher_async');
        $this->language = $container->get('language_fetcher_async');
        $this->menu = $container->get('menu_fetcher_async');
        $this->form = $container->get('form_manager');
        $this->request = $container->get('router_request');
        $this->player = $container->get('player');
        $this->playerSession = $container->get('player_session');
        $this->scripts = $container->get('scripts');
        $this->lang = $container->get('lang');
        $this->translationManager = $container->get('translation_manager');
        $this->asset = $container->get('asset');
        $this->product = $container->get('product_default');
        $this->playerDetails = $container->get('player_details');
        $this->configManager = $container->get('configuration_manager');
        $this->configFetcher = $container->get('config_fetcher');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'base' => $this->config->getGeneralConfigById('header_configuration'),
            'login' => $this->config->getGeneralConfigById('login_configuration'),
            'language' => $this->language->getLanguages(),
            'main_menu' => $this->menu->getMultilingualMenu('product-menu'),
            'secondary_menu' => $this->menu->getMultilingualMenu('secondary-menu'),
            'cashier_menu' => $this->menu->getMultilingualMenu('cashier-menu'),
            'profile_menu' => $this->menu->getMultilingualMenu('profile-menu'),
            'logger' => $this->config->getGeneralConfigById('log_configuration'),
            'vip_config' => $this->config->getGeneralConfigById('vip_configuration'),
            'vip_level' => $this->playerDetails->getPlayerVipLevel(),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        $result = $data['base'];

        // attach current uri on twig
        $uri = Url::generateFromRequest($this->request, $this->request->getUri()->getPath());
        if ($this->request->getAttribute('original_url')) {
            $uri = Url::generateFromRequest($this->request, $this->request->getAttribute('original_url'));
        }

        $host  = $this->request->getUri()->getHost();
        $result['uri'] = $uri;
        $result['host'] = $host;

        // build canonical url
        $path = $this->request->getUri()->getPath();
        $lang = $this->lang;
        $product = $this->product;
        $result['canonical']['uri'] = "https://www.dafabet.com/$lang/$product";

        $result['timestamp'] = $this->getTimestamp();
        $result['secondary_menu'] = $data['secondary_menu'] ?? [];
        $result['login'] = $data['login'];
        $result['language'] = $data['language'];
        $result['main_menu'] = $data['main_menu'];

        $result['login_form'] = $this->form->getForm(LoginForm::class)->createView();
        $result['LoginLightboxForm'] = $this->form->getForm(LoginLightboxForm::class)->createView();

        // Logger configuration
        $result['logger_disable'] = $data['logger']['disable_logging'] ?? 1;
        $result['logger_url'] = $data['logger']['logging_url'] ?? "";

        $isLogin = $this->playerSession->isLogin();

        if ($isLogin) {
            $result['cashier_menu'] = $this->disableCashierMenuItem($data['cashier_menu']);
            $result['profile_menu'] = $data['profile_menu'];

            // Get vip level for header post login
            $result['vip_level'] = $data['vip_level'] ?? [];

            if ($result['vip_level']) {
                $vipConfig = Config::parseCommaDelimited($data['vip_config']['vip_badge_tooltip']);
                $result['vip_tooltip_text'] = $vipConfig[$result['vip_level']][0];
            }
        }

        // Translation for the More on main menu navigation.
        $mainMenuConfig = $this->translationManager->getTranslation('flex-nav');

        // attach scripts to page
        $this->scripts->attach([
            'balanceExclusion' => \App\Utils\DCoin::getBalanceExclusions($result),
            'balanceError' => $result['balance_error_text'] ?? 'Error retrieving',
            'balanceErrorProduct' => $result['balance_error_text_product'] ?? 'N/A',
            'balanceLoader' => $this->asset->generateAssetUri('images/balance-loader-white.gif'),
            'loginFormConfig' => $result['login'],
            'loginConfig' => $result['login'],
            'timestamp' => $result['timestamp'],
            'mainMenuConfig' => $mainMenuConfig,
            'v2' => $this->banner_v2_enable(),
            'capsLockNotification' => $result['capslock_notification'] ?? '',
            'logger_disable' => $result['logger_disable'],
            'logger_url' => $result['logger_url'],
            'vipLevel' => $result['vip_level'] ?? [],
            'regLang' => $this->getPlayerLanguage()
        ], $options);

        return $result;
    }

    /**
     * Gets the Enable V2 from the configuration
     */
    private function banner_v2_enable()
    {

        $data = [];
        $bannerConfig = $this->configFetcher->getConfig('webcomposer_config.floating_banner_configuration');
        $data['banner_v2_enable'] = $bannerConfig['banner_v2_enable'] ?? false;

        return $data;
    }


    /**
     * Gets the timestamp and the offset
     *
     * @return array
     */
    private function getTimestamp()
    {
        $countryCode = $_SERVER['HTTP_X_CUSTOM_LB_GEOIP_COUNTRY'] ?? null;
        $timestamp = new DateTime();
        $timezone = $timestamp->getTimezone();
        $offset = $timezone->getOffSet($timestamp) / 3600;

        if ($countryCode) {
            $timezones = @DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $countryCode);

            if ($timezones) {
                $timezone = new DateTimeZone($timezones[0]);
                $timestamp->setTimezone($timezone);
                $offset = $timezone->getOffSet($timestamp) / 3600;
            }
        }

        $data['time'] = $timestamp->format(self::TIMESTAMP_FORMAT);
        $data['offset'] = $offset;

        return $data;
    }

    private function getPlayerLanguage()
    {
        $values = $this->configManager->getConfiguration('player-language');
        $language = reset($values['player-language']);

        if ($this->playerSession->isLogin()) {
            $regLang = (string) $this->player->getLocale();

            if (!empty($values['player-language']) && $regLang) {
                $language = $values['player-language'][$regLang];
            }
        }

        return $language;
    }

    /**
     *
     */
    private function disableCashierMenuItem($menuItems)
    {
        foreach ($menuItems as $key => $menuItem) {
            $currencies = explode(',', ($menuItem['attributes']['disabled'] ?? ''));

            if ($currencies &&
                !is_null($this->player->getCurrency()) &&
                in_array($this->player->getCurrency(), $currencies)
            ) {
                unset($menuItems[$key]);
            }
        }

        return $menuItems;
    }
}
