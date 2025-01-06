<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class BetConstructController extends BaseController
{
    /**
     * Get Lobby URL for BetConstruct
     */
    public function getBetConstructLobby($request, $response, $args)
    {
        $data['lobbyUrl'] = false;
        $languageCode = $request->getParam('languageCode');
        $config['icore'] =   $this->get('config_fetcher')->getGeneralConfigById('icore_games_integration');
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_bc', [
                'options' => [
                    'languageCode' => $languageCode,
                    'containerId' => $config['icore']['betconstruct_container_id'],
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
