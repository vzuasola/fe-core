<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class PGSoft extends BaseGameController
{
    const DRUPAL_KEY = 'pgsoft';

    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        $params[] = [
            'Key' => 'BetType',
            'Value' => '1'
        ];

        $params[] = [
            'Key' => 'GetLaunchURLHTML',
            'Value' => $args['launcherType'] ? 'true' : 'false'
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
                $this->provider = $this->get('game_provider_fetcher');
                $manageProvider = $this->get('game_provider_manager')->getProviders()['pgsoft'];

                $responseData = $this->get('game_provider_fetcher')
                    ->getGameUrlById('icore_pgs', $gameCode, [
                        'options' => [
                            'languageCode' => $request->getParam('languageCode', ''),
                            'playMode'  => '1',
                            'htmlParam' => $args['launcherType'] ? 'true' : 'false'
                        ]
                    ]);

                if ($responseData) {
                    $url = $manageProvider->overrideGameUrl($request, $responseData['url']);
                    $data['gameurl'] = $url ?? $responseData['url'];
                }
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
