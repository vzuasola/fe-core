<?php

namespace App\GameProvider\Gpi;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Drupal\Config;

/**
 * Provider Class for GPI Ladder
 */
class GpiArcadeProvider implements GameProviderInterface
{
    const COOKIE = 'currency';
    const KEY = 'gpi_arcade';

    /**
     * Main domain
     *
     * @var string
     */
    private $domain;

    public function setContainer($container)
    {
        $this->scripts = $container->get('scripts');
        $this->configFetcher = $container->get('config_fetcher');
        $this->playerSession = $container->get('player_session');
        $this->userFetcher = $container->get('user_fetcher');
        $this->domain = $container->get('settings')['session_handler']['cookie_domain'];
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $config =  $this->configFetcher->getGeneralConfigById('games_gpi_provider');

            if ($config) {
                $configCurrency = explode("\r\n", $config[self::KEY . '_currency'] ?? '');
                $configCountry = explode("\r\n", $config[self::KEY . '_country'] ?? '');
                $this->scripts->attach([
                    'gpi_arcade' => [
                        'currencies' => $configCurrency,
                        'countries' => $configCountry,
                        'languages' => Config::parse($config[self::KEY . '_language_mapping'] ?? ''),
                    ]
                ]);
            }
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
