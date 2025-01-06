<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class Voidbridge extends BaseGameController
{
    const DRUPAL_KEY = 'voidbridge';

    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        $portalName = $request->getParam('providerProduct', null) ?? $this->get('settings')['product'];
        $params[] = [
            'Key' => 'UserAgent',
            'Value' => $requestData['userAgent'] ?? ''
        ];

        // Live Dealer direct table parameters only
        if ("live-dealer" === $portalName) {
            $gameCode = $args['gameid'] ?? null;
            $params[] = [
                'Key' => 'Gateway',
                'Value' => $gameCode
            ];
    
            $tableName = $request->getParam('view', null);
            $params[] = [
                'Key' => 'View',
                'Value' => $tableName
            ];
        }

        return $params;
    }

    protected function getGameLobby(Request $request, Response $response)
    {
        $data['gameurl'] = false;

        try {
            $headerUA = $request->getHeaderLine('User-Agent');
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_vb', [
                'options' => [
                    'languageCode' => $request->getParam('languageCode', ''),
                    // Try to get any parameters, else use the header
                    'userAgent' => $request->getParam('userAgent', $headerUA ? $headerUA : null),
                    'providerProduct' => $request->getParam('providerProduct', null),
                    'lobbyCode' => $request->getParam('lobbyCode', null),
                    'view' => $request->getParam('view', null),
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
        try {
            $gameCode = $args['gameid'] ?? false;
            $headerUA = $request->getHeaderLine('User-Agent');
            $providerProduct = $request->getParam('providerProduct', null);
            $view = $request->getParam('view', null);
            // Check for game code
            if (!$gameCode) {
                throw new \Exception('GameCode is invalid');
            }
            // Acquire game url
            $responseData = $this->get('game_provider_fetcher')
                ->getGameUrlById('icore_vb', $gameCode, [
                    'options' => [
                        'languageCode' => $request->getParam('languageCode', ''),
                        // Try to get any parameters, else use the header
                        'userAgent' => $request->getParam('userAgent', $headerUA ? $headerUA : null),
                        'providerProduct' => $providerProduct,
                        'view' => $view,
                    ]
                ]);
            // Check for URL presence
            if (!isset($responseData['url'])) {
                // Raise exception
                throw new \Exception('No url from response');
            }
            // Set data
            $data['gameurl'] = $responseData['url'];
        } catch (\Exception $e) {
            $data['gameurl'] = false;
            // Get error message
            $errorMessage = $this->getGameErrorMessage($request, $responseData ?? []);
            // Patch data
            $data['errors'] = [
                'errorCode' => $errorMessage['errorCode'],
                'errorMessage' => $errorMessage['errorMessage'],
                'errorButton' => $errorMessage['errorButton'],
            ] ;
        }

        return $this->get('rest')->output($response, $data);
    }
}
