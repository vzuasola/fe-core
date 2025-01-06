<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class PTPlus extends BaseGameController
{
    /**
     * Provider key to be used in fetchResource();
     */
    const DRUPAL_KEY = 'ptplus';

    /**
     * Builds additional parameter list to be sent to provider
     * @param Request $request
     * @param $args
     * @return array[]
     */
    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        list($gameCode, $gameType) = array_pad(explode('|', $args['gameid']), 2, null);
        return [
            [
                'Key' => 'GameCode',
                'Value' => $gameCode
            ],
            [
                'Key' => 'GameType',
                'Value' => $gameType
            ],
            [
                'Key' => 'GameMode',
                'Value' => 1
            ]
        ];
    }

    /**
     * Get Loby URL
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    protected function getGameLobby(Request $request, Response $response)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $gameCode = $request->getParam('lobbyCode') ?? false;
        $options = ['languageCode' => $languageCode];

        if (!empty($gameCode)) {
            $options['gameCode'] = $gameCode;
        }

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_ptplus', [
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
     * Get Game URL method
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');

        try {
            $params = explode('|', $args['gameid']);
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_ptplus', $params[0], [
                'options' => [
                    'languageCode' => $languageCode,
                    'gameType' => $params[1] ?? null
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
