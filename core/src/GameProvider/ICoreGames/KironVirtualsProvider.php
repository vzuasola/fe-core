<?php

namespace App\GameProvider\ICoreGames;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Drupal\Config;

/**
 * Provider Class for Kiron Virtuals
 */
class KironVirtualsProvider implements GameProviderInterface
{
    /**
     * Kiron game code definition
     */
    const KEY = 'kiron_virtual_sports';

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
        $this->playerInfo = $container->get('player');
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
                $currency = $this->playerInfo->getCurrency();
                setcookie(self::COOKIE, $currency, null, '/', $this->domain);

                $this->scripts->attach([
                    'kiron_virtual_sports' => [
                        'currencies' => $config['kiron_virtual_sports_currency'],
                        'languages' => $config['kiron_virtual_sports_language_mapping'],
                    ],
                ]);
            }

            $this->scripts->attach([
                'game_provider' => [
                    'kiron_virtuals' => [
                        'countries' => $config['kiron_virtual_sports_country'],
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
