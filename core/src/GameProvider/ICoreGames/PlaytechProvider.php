<?php

namespace App\GameProvider\ICoreGames;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Drupal\Config;

/**
 * Provider Class for Playtech
 */
class PlaytechProvider implements GameProviderInterface
{
    const KEY = 'dafabetgames';

    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->scripts = $container->get('scripts');
        $this->configFetcher = $container->get('config_fetcher');
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        try {
            list($config, $uclConfig) = [
                $this->configFetcher->getGeneralConfigById('icore_playtech_provider'),
                $this->configFetcher->getGeneralConfigById('unsupported_currency')
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
                    'iapiConfOverride' => Config::parse($config[self::KEY . '_iapiconf_override'] ?? ''),
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
}
