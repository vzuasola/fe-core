<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Utils\IP;
use App\Utils\Url;

class PushNotificationController extends BaseController
{
    const REPLY = 'reply';
    const EVENTBUS = 'eventbus';

    /**
     * array
     */
    private $config = [];

    /**
     * string
     */
    private $playerLocale;

    /**
     * string
     */
    private $currentPath;

    /**
     * Get configurations for push notification
     */
    public function getPushConfig($request, $response, $args)
    {
        $blockUtils = $this->get('block_utils');

        // External pushnx js validate token and enable cors
        if ($request->getParam('token') && $this->externalAuth($request->getParam('token'))) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        }

        $hasWebsocket = $request->getParam('ws');
        $this->currentPath = $request->getParam('path');

        $locale = $this->getPlayerLanguage();
        $this->playerLocale = strtolower($locale);
        $useLang = $this->playerLocale;

        try {
            $languages = $this->get('language_fetcher')->getLanguages();
            $key = array_search($useLang, array_column($languages, 'prefix'));

            if ($key !== false) {
                $this->config_fetcher = $this->get('config_fetcher')->withLanguage($useLang);
                $this->config = $this->config_fetcher->getGeneralConfigById('pushnx_configuration_v2');
            } else {
                // language not existing
                // use default language to get the config from fetcher
                $useLang = $languages['default']['prefix'];
                $this->config_fetcher = $this->get('config_fetcher')->withLanguage($useLang);
                $this->config = $this->config_fetcher->getGeneralConfigById('pushnx_configuration_v2');

                // override $this->config with default yaml config for non-supported language
                $replace = $this->getDefaultTranslation($this->playerLocale);
                $this->config = array_replace($this->config, $replace);
            }
        } catch (\Exception $e) {
            // do nothing
        }

        if (!$this->isPushnxEnabled()) {
            return $this->get('rest')->output($response, ['enabled' => 0]);
        }

        if ($blockUtils->isVisibleOn($this->config['exclude_pages'], $this->currentPath)) {
            return $this->get('rest')->output($response, ['enabled' => 0]);
        }

        try {
            $playerId = $this->get('player')->getPlayerId();
            $productId = $this->get('player')->getProductId();

            $data['enabled'] = 1;
            $data['playerId'] = $playerId;
            $data['productId'] = $productId;
            $data['clientIP'] = IP::getIpAddress();
            $data['lang'] = $this->playerLocale;
        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $secureToken = $this->get('session_fetcher')->getAuthToken();
            $data['token'] = $secureToken['token'];
        } catch (\Exception $e) {
            throw $e;
        }

        $domain = $this->config['domain'] ?? '';
        $data['eventBus'] = $this->getURI($hasWebsocket, $response, $domain)[self::EVENTBUS];
        $data['replyUri'] = $this->getURI($hasWebsocket, $response, $domain)[self::REPLY];
        $data['dateformat'] = [
            'format' => $this->config['date_format'],
            'offset' => $this->config['date_offset']
        ];
        $data['delayCount'] = $this->config['delay_count'];
        $data['displayAllMessage'] = $this->config['debug_display_all'];
        $data['displayExpiryDate'] = $this->config['debug_display_expirydate'];
        $data['expiryDelayCount'] = $this->config['expiry_delay_count'];
        $data['logging'] = $this->config['debug_logging'];

        $products = $this->configProduct($this->config['product_list']);

        $data['productTypeId'] = $products['productTypeId'];
        $data['productDetails'] = $products['products'];

        $data['retryCount'] = $this->config['retry_count'];

        $data['dismiss']['button_label'] = $this->config['dismiss_button_label'];
        $data['dismiss']['content'] = $this->config['dismiss_content']['value'];
        $data['dismiss']['yes'] = $this->config['dismiss_yes'];
        $data['dismiss']['no'] = $this->config['dismiss_no'];

        $data['texts']['title'] = $this->config['title'];
        $data['texts']['empty'] = $this->config['empty'];
        $data['texts']['expired_message'] = $this->config['expired_message'];
        $data['texts']['copy_to_clipboard'] = $this->config['copied'];

        $data['disableBonusAward'] = $this->config['disableBonusAward'] ?? 0;

        $ctabuttons = $this->configButtons($this->config['cta_button_list']);

        $data['cta'] = $ctabuttons;

        if ($this->config['domains']) {
            $domains = $this->parseDomains($this->config['domains']);
        } else {
            $domains = [];
        }

        $data['pushnx_domains'] = $domains;

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Get site language based
     */
    private function getPlayerLanguage()
    {
        $playerLang = $this->get('configuration_manager')->getConfiguration('player-language');
        $language = reset($playerLang['player-language']);
        $regLang = (string) $this->get('player')->getLocale();

        if (!empty($playerLang['player-language'])
            && $regLang && isset($playerLang['player-language'][$regLang])) {
            $language = $playerLang['player-language'][$regLang];
        }

        return $language;
    }

    /**
     * Get site language based
     */
    private function getDefaultTranslation($lang)
    {
        $defaultTrans = $this->get('configuration_manager')->getConfiguration('push-notification');
        $translation = reset($defaultTrans['translation']);

        if ($lang && isset($defaultTrans['translation'][$lang])) {
            $translation = $defaultTrans['translation'][$lang];
        }

        return $translation;
    }

    /**
     * Get eventbus and reply URI
     */
    private function getURI($hasWebsocket, $response, $domainConfig)
    {
        $parameters = $this->get('parameters');
        $replyPrefix = $parameters['pushnx.api.reply.prefix'];
        $eventbusPrefix = $parameters['pushnx.api.eventbus.prefix'];
        $fallbackPrefix = $parameters['pushnx.fallback.prefix'];

        // Get the domain from configuration if not empty, else from parameters
        $domain = empty($domainConfig) ? $parameters['pushnx.server'] : $domainConfig;
        // If browser has no web socket, use fallback path
        $lang = $response->getHeader('Content-Language');
        $domain = $hasWebsocket == 'false' ? "/$lang[0]$fallbackPrefix" : $domain;

        return [
            self::EVENTBUS => "$domain$eventbusPrefix",
            self::REPLY => "$domain$replyPrefix",
        ];
    }

    /**
     * Check if a push notification lightbox is visible on the current page.
     *
     * @return boolean
     */
    private function visibility()
    {
        $pages = $this->config['exclude_pages'];
        // if it is empty, show it
        if (empty($pages)) {
            return true;
        }
        $pages = explode(PHP_EOL, $pages);

        $path = rawurldecode($this->currentPath);
        $path = trim($path);

        // Trim leading and trailing slash if not front page.
        if ($path !== '/') {
            $path = trim($path, '/');
        }

        if ($pages) {
            foreach ($pages as $page) {
                $page = trim($page);
                // Trim leading and trailing slash to accurately match the current path.
                if ($page !== '/') {
                    $page = trim($page, '/');
                }
                // Considered as homepage.
                if ($page == '<front>') {
                    $page = '/';
                }

                if ($page == $path || fnmatch($page, $path)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if push notification is enabled
     */
    private function isPushnxEnabled()
    {
        if (!$this->config['enable']) {
            return false;
        }

        if ($this->get('player_session')->isLogin() && $this->visibility()) {
            return true;
        }

        return false;
    }

    /**
     * Validate ICore token from external pushnx
     */
    public function externalAuth($token)
    {
        return $this->get('player_session')->authenticateByToken($token);
    }

    private function configProduct($productlist)
    {
        $texts = array_map('trim', explode(PHP_EOL, $productlist));
        $texts = str_replace(' ', '', $texts);

        foreach ($texts as $text) {
            $identifier = strtolower($text);

            if (!$this->config['product_exclude_' . $identifier]) {
                if (!empty($this->config['product_type_id_' . $identifier])) {
                    $index = $this->config['product_type_id_' . $identifier];
                } else {
                    $index = '0';
                }

                $details['productTypeId'][] = $index;

                $details['products'][$index] = [
                    'label' => $this->config['product_label_' . $identifier],
                    'typeid' => $this->config['product_type_id_' . $identifier],
                    'icon' => $this->config['product_icon_' . $identifier],
                    'allowtodismiss' => $this->config['product_exclude_dismiss_'. $identifier]
                ];
            }
        }

        return $details;
    }

    private function configButtons($buttonlist)
    {
        $texts = array_map('trim', explode(PHP_EOL, $buttonlist));
        $texts = str_replace(' ', '', $texts);

        foreach ($texts as $text) {
            $identifier = strtolower($text);

            $details['buttons'][$identifier] = [
                'label' => $this->config['cta_label_' . $identifier],
                'action' => $this->actionToken($this->config['cta_actions_' . $identifier]),
            ];
        }

        return $details;
    }

    private function actionToken($action)
    {
        if ($action) {
            $arr = explode('::', $action);

            if (count($arr) > 1) {
                $domain = $this->get('token_parser')->processTokens($arr[1]);

                $arr[1] = $domain;

                return implode('::', $arr);
            }
        }

        return $action;
    }

    private function parseDomains($domains)
    {
        $map = array_map('trim', explode(PHP_EOL, $domains));

        foreach ($map as $value) {
            list($key, $domain) = explode('|', $value);

            $parsed = $this->get('token_parser')->processTokens($domain);
            $mapped[$key] = $parsed;
        }

        return $mapped;
    }
}
