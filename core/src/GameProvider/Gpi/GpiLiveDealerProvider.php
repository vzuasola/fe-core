<?php

namespace App\GameProvider\Gpi;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Drupal\Config;

/**
 * Provider Class for GPI Live Dealer
 */
class GpiLiveDealerProvider implements GameProviderInterface
{
    const COOKIE = 'currency';
    const KEY = 'gpi_live_dealer';

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
        $isLogin = $this->playerSession->isLogin();
        try {
            $config =  $this->configFetcher->getGeneralConfigById('games_gpi_provider');

            if ($isLogin && $config) {
                $configCurrency = explode("\r\n", $config[self::KEY . '_currency'] ?? '');
                $this->scripts->attach([
                    'gpi_live_dealer' => [
                        'currencies' => $configCurrency,
                        'languages' => Config::parse($config[self::KEY . '_language_mapping'] ?? '')
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
