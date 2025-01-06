<?php

namespace App\GameProvider\Exchange;

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
        $exchangeConfig =  $this->configFetcher->getGeneralConfigById('games_exchange_provider');

        if ($isLogin) {
            $data = [
                'exchange_user_currencies' => $this->playerInfo->getCurrency(),
                'exchange_game_url' => $exchangeConfig['exchange_game_url'],
                'exchange_currency' => $exchangeConfig['currency'],
                'exchange_languages' =>$exchangeConfig['languages'],
            ];
        }
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
