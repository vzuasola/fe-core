<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

use App\Drupal\Config;
use App\Form\LoginForm;
use App\Form\LoginLightboxForm;
use App\Utils\Url;
use App\Utils\Menu;

use DateTime;
use DateTimeZone;

class Header extends CommonSectionBase implements AsyncSectionInterface
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
        parent::setContainer($container);

        $this->form = $container->get('form_manager');
        $this->request = $container->get('router_request');
        $this->player = $container->get('player');
        $this->playerSession = $container->get('player_session');
        $this->scripts = $container->get('scripts');
        $this->lang = $container->get('lang');
        $this->translationManager = $container->get('translation_manager');
        $this->product = $container->get('product_default');
        $this->asset = $container->get('asset');
        $this->config = $container->get('config_fetcher_async');
        $this->playerDetails = $container->get('player_details');
        $this->configManager = $container->get('configuration_manager');
        $this->userPreference = $container->get('preferences_fetcher');
        $this->configFetcher = $container->get('config_fetcher');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        $definition = parent::getSectionDefinition($options);

        $definition['logger'] = $this->config->getGeneralConfigById('log_configuration');
        $definition['vip_level'] = $this->playerDetails->getPlayerVipLevel();
        $definition['geoip_language_popup'] = $this->config->getGeneralConfigById('geoip_language_popup');

        return $definition;
    }

    /**
     *
     */
    public function processDefinition($data, array $options)
    {
        $result = [];
        $data = parent::processDefinition($data, $options);

        if (isset($data['base']['header'])) {
            $data['base']['header']['logger'] = $data['logger'];
            $data['base']['header']['vip_level'] = $data['vip_level'];
            $data['base']['header']['geoip_language_popup'] = $data['geoip_language_popup'];

            $result = $this->getSectionData($data['base']['header'], $options);
        }

        return $result;
    }

    /**
     *
     */
    protected function getSectionData($data, array $options)
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
        $dir = isset($path) ? ltrim($path, '/') : '';
        $lang = $this->lang;
        $product = $this->product;
        $result['canonical']['uri'] = "https://www.dafabet.com/$lang/$product/$dir";

        $result['timestamp'] = $this->getTimestamp();
        $result['login'] = $data['login'] ?? [];
        $result['language'] = $this->filterLanguageSelector($data['language'] ?? []);
        $result['main_menu'] = $data['main_menu'] ?? [];

        // Set active class on secondary menu
        $result['secondary_menu'] = Menu::setActiveClass($this->request, $data['secondary_menu'] ?? []);

        $result['login_form'] = $this->form->getForm(LoginForm::class)->createView();
        $result['LoginLightboxForm'] = $this->form->getForm(LoginLightboxForm::class)->createView();

        // Logger configuration
        $result['logger_disable'] = $data['logger']['disable_logging'] ?? 1;
        $result['logger_url'] = $data['logger']['logging_url'] ?? "";

        $isLogin = $this->playerSession->isLogin();

        if ($isLogin) {
            $result['cashier_menu'] = $this->disableCashierMenuItem($data['cashier_menu']) ?? [];
            $result['profile_menu'] = $data['profile_menu'] ?? [];

            // Get vip level for header post login
            $result['vip_level'] = $data['vip_level'] ?? [];

            if ($result['vip_level']) {
                $vipConfig = Config::parseCommaDelimited($data['vip_config']['vip_badge_tooltip'] ?? '');
                $result['vip_tooltip_text'] = $vipConfig[$result['vip_level']][0] ?? '';
            }
        }

        //Translation for the More on main menu navigation.
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
            'regLang' => $this->getPlayerLanguage(),
            'geoIpLanguagePopup' => $this->getGeoIPLanguagePopupData($data['geoip_language_popup'])
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

    /**
     * GeoIP language popup settings and user data
     */
    private function getGeoIPLanguagePopupData($geoIpConfig)
    {
        $geoIp = $this->request->getHeaderLine('x-custom-lb-geoip-country') ?? null;
        if ($geoIp && $this->playerSession->isLogin()) {
            $geoIp = strtolower($geoIp);
            $geoIpListConfig = array_map('trim', explode(PHP_EOL, $geoIpConfig['geoip_list'] ?? ""));
            $geoIpList = array_filter($geoIpListConfig, function ($geoIp) {
                return !empty($geoIp);
            });

            if (in_array($geoIp, $geoIpList)) {
                $geoIp = strtolower($geoIp);
                $key = 'dafabet.language.popup.geoip';
                $showPopup = true;

                $existingData = $this->userPreference->getPreferences()[$key] ?? false;

                if ($existingData && in_array($geoIp, $existingData)) {
                    $showPopup = false;
                }

                return [
                    'message' => $geoIpConfig["{$geoIp}_popup_content"] ?? null,
                    'show' => $showPopup
                ];
            }
        }
    }

    public function filterLanguageSelector($languages)
    {
        return array_filter($languages, function ($l) {
            return isset($l['hide']) && $l['hide'] ? false : true;
        });
    }
}
