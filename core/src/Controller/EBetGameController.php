<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class EBetGameController extends BaseController
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
        // ddd($languageCode);
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_ebet', [
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

            $responseData = $this->provider->getGameUrlById('icore_ebet', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode,
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
