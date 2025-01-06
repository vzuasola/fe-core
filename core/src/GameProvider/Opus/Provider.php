<?php

namespace App\GameProvider\Opus;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Plugins\GameProvider\GameProviderInterface;
use App\Utils\Url;

/**
 *
 */
class Provider implements GameProviderInterface
{
    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->playerSession = $container->get('player_session');
        $this->configFetcher = $container->get('config_fetcher');
        $this->userFetcher = $container->get('user_fetcher');
        $this->scripts = $container->get('scripts');
        $this->playerInfo = $container->get('player');
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        $data = [];

        $isLogin = $this->playerSession->isLogin();
        $opusConfig =  $this->configFetcher->getGeneralConfigById('games_opus_provider');

        if ($isLogin) {
            $data = [
                'opus_currencies' => $this->playerInfo->getCurrency(),
                'opus_game_url' => $opusConfig['opus_game_url'],
                'opus_alternate_game_url' => $opusConfig['opus_alternative_game_url'],
                'opus_free_play_url' => $opusConfig['opus_game_free_play_url'],
                'opus_currency' => $opusConfig['currency'],
                'opus_languages' => $opusConfig['languages'],
            ];
        } else {
            $data = [
                'opus_free_play_url' => $opusConfig['opus_game_free_play_url']
            ];
        }

        $this->scripts->attach([
            'game_provider' => [
                'opus' => [
                    'countries' => $opusConfig['country'] ?? []
                ]
            ]
        ], true);

        $this->scripts->attach($data);
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
