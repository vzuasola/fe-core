<?php

namespace App\GameProvider\ICoreGames;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provider Class for Video Racing
 */
class VideoRacingProvider implements GameProviderInterface
{
    /**
     * Video Racing game code definition
     */
    const ICORE_GAME_PROVIDER = 'vr';

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
                    'icore_games' => [
                        'video_racing' => [
                            'currencies' => $config['video_racing_currency'],
                            'languages' => $config['video_racing_language_mapping']
                        ]
                    ]
                ]);
            }
              $this->scripts->attach([
                        'game_provider' => [
                            'video_racing' => [ 'countries' => $config['video_racing_country']
                            ]
                        ]
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
