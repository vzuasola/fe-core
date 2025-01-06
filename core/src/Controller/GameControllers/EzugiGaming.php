<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

/**
 * Controller for Ezugi Live Dealer game
 */
class EzugiGaming extends BaseGameController
{
    const DRUPAL_KEY = 'ezugi_gaming';

    /**
     * Append Properties needed for Direct Table launch via PlayerGame API
     */
    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        $tableName = $request->getParam('tableName') ?? null;
        $params[] = [
            'Key' => 'openTable',
            'Value' => $tableName
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
        $tableName = $request->getParam('tableName');
        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_ezugi', [
                'options' => [
                    'languageCode' => $languageCode,
                    'tableName' => $tableName
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
}
