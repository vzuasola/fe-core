<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class FunGaming extends BaseGameController
{
    const DRUPAL_KEY = 'fun_gaming';

    /**
     * Get PlayerGame Extra Params
     */
    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        $gameCode = $args['gameid'] ?? null;
        $params[] = [
            'Key' => 'FGGameCode',
            'Value' => $gameCode
        ];

        return $params;
    }

    /**
     * Get Game URL
     */
    protected function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        try {
            $gameCode = $args['gameid'] ?? false;
            if ($gameCode) {
                $responseData = $this->get('game_provider_fetcher')
                    ->getGameUrlById('icore_fg', $gameCode, [
                        'options' => [
                            'languageCode' => $request->getParam('languageCode', ''),
                            'returnUrl' => $request->getParam('returnUrl', '')
                        ]
                    ]);

                $data['gameurl'] = $responseData['url'] ?? false;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
