<?php

namespace App\Controller\GameControllers;

use App\BaseController;
use App\Slim\Response;
use Slim\Http\Request;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class OneGame extends BaseGameController
{
    const DRUPAL_KEY = 'onegame';

    /**
     * Get Lobby URL
     */
    public function getGameLobby(Request $request, Response $response)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $gameCode = $request->getParam('gameCode') ?? false;
        $options = ['languageCode' => $languageCode];

        if (!empty($gameCode)) {
            $options['gameCode'] = $gameCode;
        }

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_onegame', [
                'options' => $options
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
    public function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct', null);

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_onegame', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode,
                    'providerProduct' => $providerProduct
                ]
            ]);

            if ($responseData) {
                $data['gameurl'] = $responseData['url'];
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
