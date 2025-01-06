<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class KYGaming extends BaseGameController
{
    const DRUPAL_KEY = 'ky_gaming';

    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        $gameCode = $args['gameid'] ?? null;
        $params[] = [
            'Key' => 'KYGameCode',
            'Value' => $gameCode
        ];

        return $params;
    }

    /**
     * Get Game URL
     */
    public function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        try {
            $gameCode = $args['gameid'] ?? false;
            if ($gameCode) {
                $responseData = $this->get('game_provider_fetcher')
                            ->getGameUrlById('icore_ky', $gameCode, [
                                'options' => [
                                    'languageCode' => $request->getParam('languageCode', '')
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
