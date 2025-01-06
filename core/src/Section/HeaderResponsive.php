<?php

namespace App\Section;

use App\Plugins\Section\SectionInterface;
use Interop\Container\ContainerInterface;
use App\Form\LoginForm;
use App\Form\LoginLightboxForm;
use App\Utils\Url;
use DateTimeZone;
use DateTime;
use App\Drupal\Config;

class HeaderResponsive implements SectionInterface
{
    /**
     * Timestamp format
     */
    const TIMESTAMP_FORMAT = 'Y/m/d H:i:s';

    /**
     * Timestamp string property
     */
    private $strTimestamp = 'timestamp';

    /**
     * Login constant string
     */
    private $login = 'login';

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->config = $container->get('config_fetcher');
        $this->language = $container->get('language_fetcher');
        $this->menu = $container->get('menu_fetcher');
        $this->form = $container->get('form_manager');
        $this->request = $container->get('router_request');
        $this->player = $container->get('player_session');
        $this->scripts = $container->get('scripts');
        $this->lang = $container->get('lang');
        $this->playerLocale = $container->get('player');
        $this->configManager = $container->get('configuration_manager');
    }

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function getSection(array $options)
    {
        try {
            $data = $this->config->getGeneralConfigById('header_configuration');
        } catch (\Exception $e) {
            $data = [];
        }

        // attach current uri on twig
        $uri = Url::generateFromRequest($this->request, $this->request->getUri()->getPath());
        $data['uri'] = $uri;

        $data[$this->strTimestamp] = $this->getTimestamp();

        try {
            $data['secondary_menu'] = $this->menu->getMultilingualMenu("secondary-menu");
        } catch (\Exception $e) {
            $data['secondary_menu'] = [];
        }

        try {
            $data[$this->login] = $this->config->getGeneralConfigById('login_configuration');
        } catch (\Exception $e) {
            $data[$this->login] = [];
        }

        try {
            $data['language'] = $this->language->getLanguages();
        } catch (\Exception $e) {
            $data['language'] = [];
        }

        try {
            $data['main_menu'] = $this->menu->getMultilingualMenu("product-menu");
        } catch (\Exception $e) {
            $data['main_menu'] = [];
        }

        $data['login_form'] = $this->form->getForm(LoginForm::class)->createView();
        $data['LoginLightboxForm'] = $this->form->getForm(LoginLightboxForm::class)->createView();

        $isLogin = $this->player->isLogin();

        if ($isLogin) {
            try {
                $data['cashier_menu'] = $this->menu->getMultilingualMenu("cashier-menu");
            } catch (\Exception $e) {
                $data['cashier_menu'] = [];
            }

            try {
                $data['profile_menu'] = $this->menu->getMultilingualMenu("profile-menu");
            } catch (\Exception $e) {
                $data['profile_menu'] = [];
            }
        }

        // attach scripts to page
        $this->scripts->attach([
            'balanceError' => $data['balance_error_text'] ?? 'Error retrieving',
            'balanceErrorProduct' => $data['balance_error_text_product'] ?? 'N/A',
            'loginFormConfig' => $data[$this->login],
            'loginConfig' => $data[$this->login],
            $this->strTimestamp => $data[$this->strTimestamp],
            'regLang' => $this->getPlayerLanguage()
        ]);

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

        if ($this->player->isLogin()) {
            $regLang = (string) $this->playerLocale->getLocale();

            if (!empty($values['player-language']) && $regLang) {
                $language = $values['player-language'][$regLang];
            }
        }

        return $language;
    }
}
