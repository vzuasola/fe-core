<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class EvoGaming extends BaseGameController
{
    const DRUPAL_KEY = 'evo_gaming';

    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        $gameCode = $args['gameid'] ?? null;
        $params[] = [
            'Key' => 'externalgameid',
            'Value' => $gameCode
        ];

        return $params;
    }
    
    /**
     * Get Lobby URL
     */
    protected function getGameLobby(Request $request, Response $response)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $category = $request->getParam('category', null);

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_evo', [
                'options' => [
                    'languageCode' => $languageCode,
                    'category' => $category
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
    protected function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct', null);
        $category = $request->getParam('category', null);

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_evo', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode,
                    'providerProduct' => $providerProduct,
                    'category' => $category
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
