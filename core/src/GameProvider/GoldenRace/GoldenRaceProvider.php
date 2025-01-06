<?php

namespace App\GameProvider\GoldenRace;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provider Class for GoldenRace
 */
class GoldenRaceProvider implements GameProviderInterface
{
    /**
     * Golden Racing game code definition
     */
    const ICORE_GAME_PROVIDER = 'gr';

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
        $this->playerSession = $container->get('player_session');
        $this->scripts = $container->get('scripts');
        $this->configFetcher = $container->get('config_fetcher');
        $this->userFetcher = $container->get('user_fetcher');
        $this->domain = $container->get('settings')['session_handler']['cookie_domain'];
        $this->playerInfo = $container->get('player');
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        $isLogin = $this->playerSession->isLogin();
        $config =  $this->configFetcher->getGeneralConfigById('games_goldenrace_provider');

        try {
            $config =  $this->configFetcher->getGeneralConfigById('games_goldenrace_provider');

            if ($isLogin && $config) {
                $currency = $this->playerInfo->getCurrency();
                setcookie(self::COOKIE, $currency, null, '/', $this->domain);
                $this->scripts->attach([
                    'golden_race' => [
                        'currencies' => $config['currency'],
                    ]
                ]);
            }
            $this->scripts->attach([
                'game_provider' => [
                    'goldenrace_virtuals' => [
                        'countries' => $config['country'],
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
        $config =  $this->configFetcher->getGeneralConfigById('games_goldenrace_provider');

        return [
            $config['javascript_assets'],
        ];
    }
}
