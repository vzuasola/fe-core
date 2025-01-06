<?php

namespace App\GameProvider\ICoreGames;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Drupal\Config;

/**
 * Provider Class for GlobalBet Virtuals
 */
class GlobalbetVirtualsProvider implements GameProviderInterface
{
    /**
     * GlobalBet game code definition
     */
    const KEY = 'globalbet_virtual_sports';

    /**
     * Cookie definition
     */
    const COOKIE = 'currency';

    /**
     * Main domain
     *
     * @var string
    */
    private $domain;

    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->scripts = $container->get('scripts');
        $this->configFetcher = $container->get('config_fetcher');
        $this->playerSession = $container->get('player_session');
        $this->domain = $container->get('settings')['session_handler']['cookie_domain'];
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        $isLogin = $this->playerSession->isLogin();

        try {
            $config =  $this->configFetcher->getGeneralConfigById('icore_games_integration');

            if ($isLogin && $config) {
                $playerDetails = $this->playerSession->getDetails();
                $currency = $playerDetails['currency'];
                setcookie(self::COOKIE, $currency, null, '/', $this->domain);

                $this->scripts->attach([
                    'globalbet_virtual_sports' => [
                        'currencies' => $config['gb_virtual_sports_currency'],
                        'languages' => $config['gb_virtual_sports_language_mapping'],
                    ],
                ]);
            }

            $this->scripts->attach([
                'game_provider' => [
                    'globalbet_virtuals' => [
                        'countries' => $config['gb_virtual_sports_country'],
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
