<?php

namespace App\GameProvider\ICoreGames;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use App\Drupal\Config;

/**
 * Provider Class for PGSoft game provider
 */
class PGSoftProvider implements GameProviderInterface
{
    const KEY = 'pgsoft';
    /**
     * Header name of geoip
     */
    const GEOIP_HEADER = 'x-custom-lb-geoip-country';

    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->scripts = $container->get('scripts');
        $this->config = $container->get('config_fetcher');
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        try {
            list($config, $uclConfig) = [
                $this->config->getGeneralConfigById('icore_games_integration'),
                $this->config->getGeneralConfigById('unsupported_currency')
            ];

            $settings = $this->scripts->getScripts();
            $configCurrency = explode("\r\n", $config[self::KEY . '_currency']);
            $configCountry = explode("\r\n", $config[self::KEY . '_country'] ?? '');
            $providerMapping = Config::parse($uclConfig['game_provider_mapping'] ?? '');

            $newSettings = [
                self::KEY => [
                    'currencies' => $configCurrency,
                    'countries' => $configCountry,
                    'languages' => Config::parse($config[self::KEY . '_language_mapping'] ?? ''),
                    'providerName' => $providerMapping[self::KEY] ?? ''
                ]
            ];

            if (isset($settings['icore_games'])) {
                $newSettings = array_merge($settings['icore_games'], $newSettings);
            }

            $this->scripts->attach([
                'icore_games' => $newSettings
            ]);
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onSessionDestroy()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascriptAssets()
    {
        return [];
    }

    /**
     * Override the game URL base from the GeoCode of the player to a different hosts for optimized gameplay
     */
    public function overrideGameUrl(RequestInterface $request, $baseUrl)
    {
        try {
            // Get GEOIP
            $geoip = $request->getHeaderLine(self::GEOIP_HEADER);

            // Get domain override config
            $config = $this->config->getGeneralConfigById('icore_games_integration');
            $configGeoIp = $this->explodeList($config[self::KEY . '_geoip_domain_override'] ?? '');

            if (isset($configGeoIp[$geoip]) && $overrideDomain = parse_url($configGeoIp[$geoip]['host'])) {
                $parsedUrl = parse_url($baseUrl);
                $parsedUrl['host'] = $overrideDomain['host'];

                // Check if there's a server_url param which will override the server_url query string
                if (isset($configGeoIp[$geoip]['server_url'])) {
                    parse_str($parsedUrl['query'], $query);
                    $query['server_url'] = $configGeoIp[$geoip]['server_url'];

                    $parsedUrl['query'] = urldecode(http_build_query($query));
                }

                $newUrl = http_build_url($parsedUrl);

                return (false === $newUrl) ? $baseUrl : $newUrl;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        // If eveything fails, return the original game URL
        return $baseUrl;
    }

    /**
     * Prepare the override values for the game URL that will base on GeoCode
     *
     * @param string $config Formatted string to be parsed
     */
    private function explodeList($config)
    {
        $nconfig = [];

        if (!empty($config)) {
            $rows = explode(PHP_EOL, $config);
            foreach ($rows as $rows) {
                list($geoCode, $host, $server_url) = explode('|', trim($rows));
                $nconfig[$geoCode] = [
                    'host' => $host,
                    'server_url' => $server_url
                ];
            }
        }

        return $nconfig;
    }

}
