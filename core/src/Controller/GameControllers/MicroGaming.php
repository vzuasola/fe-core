<?php

namespace App\Controller\GameControllers;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class MicroGaming extends BaseController
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
     * Get Game URL
     */
    public function getGameUrl($request, $response, $args)
    {
        $data['gameurl'] = false;
        try {
            $gameCode = $args['gameid'] ?? false;
            if ($gameCode) {
                $responseData = $this->get('game_provider_fetcher')
                            ->getGameUrlById('icore_mg', $gameCode, [
                                'options' => [
                                    'languageCode' => $request->getParam('languageCode', ''),
                                    'playMode'  => '1' == $request->getParam('playMode', '1') ? 'true':'false',
                                    'providerProduct' => $request->getParam('providerProduct', null)
                                ]
                            ]);

                $data['gameurl'] = $responseData['url'] ?? false;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Get Lobby URL
     */
    public function getGameLobby($request, $response)
    {
        $data['gameurl'] = false;
        try {
            $responseData = $this->get('game_provider_fetcher')
                            ->getLobby('icore_mg', [
                                'options' => [
                                    'languageCode' => $request->getParam('languageCode', ''),
                                ]
                            ]);
            $data['gameurl'] = $responseData ?? false;
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
