<?php

namespace App\Localization;

/**
 * Checks and detect any langauge localization
 */
class Localization
{
    /**
     * Header name of geoip
     */
    const GEOIP_HEADER = 'x-custom-lb-geoip-country';

    /**
     * Request object
     */
    protected $request;

    /**
     * Configuration
     */
    protected $configuration;

    /**
     * Settings
     */
    protected $settings;

    /**
     * Session object
     */
    protected $playerSession;

    /**
     * Current language
     */
    protected $langPrefix;

    /**
     * Static variable for the local language value
     */
    private static $localLanguage = null;

    /**
     * Public constructor
     *
     * @param Request $request
     * @param YamlConfiguration $configuration
     * @param Settings $settings
     * @param Request $langPrefix
     * @param PlayerSession $playerSession
     * @param Player $player
     */
    public function __construct($request, $configuration, $settings, $langPrefix, $playerSession, $player)
    {
        $this->request = $request;
        $this->configuration = $configuration;
        $this->settings = $settings;
        $this->langPrefix = $langPrefix;
        $this->playerSession = $playerSession;
        $this->player = $player;
    }

    /**
     * Override the request object
     * Warning: This might affect the process of the whole request lifecycle
     *
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get the localized language from request object
     *
     * @return mixed boolean|string
     */
    public function getLocalLanguage()
    {
        try {
            // Check if localization is enabled
            $enabled = $this->settings['localization']['enable'] ?? false;
            if (!$enabled) {
                return false;
            }

            if (null === self::$localLanguage) {
                $isLogin = $this->playerSession->isLogin();
                if (!$isLogin) {
                    self::$localLanguage = $this->getLocalLanguageByGeoIp();
                } else {
                    self::$localLanguage = $this->getLocalLanguageByCurrency();
                }
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return self::$localLanguage;
    }

    /**
     * Set a custom response header for marked localized content
     *
     * @see App\Localization\Middleware\Request\Localization to add this header on all requests
     * @return $this
     */
    public function setResponseHeader(&$response)
    {
        if ($localized = $this->getLocalLanguage()) {
            $response = $response->withHeader('X-Localized-Content', $localized);
        }

        return $this;
    }

    /**
     * Get the local language by geoip
     *
     * @return mixed boolean|string
     */
    protected function getLocalLanguageByGeoIp()
    {
        $geoip = $this->request->getHeaderLine(self::GEOIP_HEADER);
        $map = $this->configuration->getConfiguration('localization')['geoip'];
        if (isset($map[$this->langPrefix]) && in_array($geoip, $map[$this->langPrefix])) {
            // We just concat the current lang and the geoIP
            // To follow the ISO 639-1 standards,
            // we'll force the localize geoip (the "-<lang_code>") to uppercase
            return $this->langPrefix . '-' . strtoupper($geoip);
        }

        return false;
    }

    /**
     * Get the local language by currency. Player object should exist (post-login).
     *
     * @return mixed boolean|string
     */
    protected function getLocalLanguageByCurrency()
    {
        $currency = $this->player->getCurrency();
        $map = $this->configuration->getConfiguration('localization')['currency'];
        if (isset($map[$this->langPrefix]) && in_array($currency, $map[$this->langPrefix])) {
            // originally it should be es-MXN
            // we'll substr the last string to get the es-MX
            return $this->langPrefix . '-' . strtoupper(substr($currency, 0, 2));
        }

        return false;
    }
}
