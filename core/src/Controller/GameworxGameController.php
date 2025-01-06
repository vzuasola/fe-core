<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class GameworxGameController extends BaseController
{
    /**
     * Get Lobby URL Lottery game
     */
    public function getLotteryGameLobby($request, $response, $args)
    {
        $data['lobbyUrl'] = false;
        $languageCode = $request->getParam('languageCode');
        $config['icore'] =   $this->get('config_fetcher')->getGeneralConfigById('icore_games_integration');
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_gw', [
                'options' => [
                    'languageCode' => $languageCode,
                    'lobbyid' => $config['icore']['gameworx_lottery_lobby_type'],
                    'pluginGameId' => $config['icore']['gameworx_lottery_plugin_id'],
                    'realitycheckurl' => $config['icore']['gameworx_lottery_realitycheck_url'],
                    'depositUrl' => $config['icore']['gameworx_lottery_deposit_url'],
                    'exitUrl' => $config['icore']['gameworx_lottery_exit_url']
                ]
            ]);

            if (strtolower($responseData['StatusCode']) === 'success') {
                $data['lobbyUrl'] = $responseData['LobbyUrl'];
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Get Lobby URL for Quick Lotto
     */
    public function getQuickLottoGameLobby($request, $response, $args)
    {
        $data['lobbyUrl'] = false;
        $languageCode = $request->getParam('languageCode');
        $config['icore'] =   $this->get('config_fetcher')->getGeneralConfigById('icore_games_integration');

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_gw', [
                'options' => [
                    'languageCode' => $languageCode,
                    'lobbyid' => $config['icore']['gameworx_quicklotto_lobby_type'],
                    'pluginGameId' => $config['icore']['gameworx_quicklotto_plugin_id'],
                    'realitycheckurl' => $config['icore']['gameworx_quicklotto_realitycheck_url'],
                    'depositUrl' => $config['icore']['gameworx_quicklotto_deposit_url'],
                    'exitUrl' => $config['icore']['gameworx_quicklotto_exit_url']
                ]
            ]);

            if (strtolower($responseData['StatusCode']) === 'success') {
                $data['lobbyUrl'] = $responseData['LobbyUrl'];
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
