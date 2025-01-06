<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class TGPGameController extends BaseController
{
    /**
     * Get Lobby URL
     */
    public function getGameLobby($request, $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        // ddd($languageCode);
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_tgp', [
                'options' => [
                    'languageCode' => $languageCode
                ]
            ]);

            if ($responseData) {
                $data['gameurl'] = $responseData;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Get Game URL
     */
    public function getGameUrl($request, $response, $args)
    {
    }
}
