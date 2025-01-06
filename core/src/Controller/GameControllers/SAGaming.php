<?php

namespace App\Controller\GameControllers;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class SAGaming extends BaseController
{
    public function fetchResource($request, $response, $args)
    {
        $resource = $args['resource'] ?? '';
        if ('lobby' === $resource) {
            return $this->getGameLobby($request, $response);
        } elseif ('url' === $resource) {
            return $this->getGameUrl($request, $response, $args);
        } else {
            throw new NotFoundException($request, $response);
        }
    }

    /**
     * Get Lobby URL
     */
    public function getGameLobby($request, $response)
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

            $responseData = $this->provider->getLobby('icore_sa', [
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
    public function getGameUrl($request, $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct', null);

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_sa', $args['gameid'], [
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
