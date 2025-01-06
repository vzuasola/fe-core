<?php

namespace App\GameProvider\ICoreGames;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use App\Drupal\Config;

/**
 * Provider Class for JSystem provider
 */
class JSystemProvider implements GameProviderInterface
{
    const KEY = 'jsystem';

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

            $this->scripts->attach([
                'game_provider' => [
                    self::KEY => [
                        'countries' => $config[self::KEY . '_country'],
                    ],
                ],
            ], true);
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
