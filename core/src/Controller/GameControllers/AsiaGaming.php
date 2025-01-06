<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

/**
 * Controller for AG/Fish hunter game
 */
class AsiaGaming extends BaseGameController
{
    const DRUPAL_KEY = 'asia_gaming';
    /**
     * Get Lobby URL
     */
    protected function getGameLobby(Request $request, Response $response)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_ag', [
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
    protected function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct', null);

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_ag', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode,
                    'providerProduct' => $providerProduct
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
