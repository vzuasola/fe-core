<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class GoldDeluxeGameController extends BaseController
{
    /**
     * Get Lobby URL
     */
    public function getGameLobby($request, $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_gd', [
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
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_gd', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode
                ]
            ]);

            if ($responseData['url']) {
                $data['gameurl'] = $responseData['url'];
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
