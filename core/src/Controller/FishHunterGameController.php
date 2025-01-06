<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @deprecated use Asian Gaming controller
 */
class FishHunterGameController extends BaseController
{
    /**
     * Get Lobby URL
     */
    public function getGameLobby($request, $response, $args)
    {
        $data['lobbyUrl'] = false;
        $languageCode = $request->getParam('languageCode');

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_fh', [
                'options' => [
                    'languageCode' => $languageCode
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
