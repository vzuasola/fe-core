<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class Skywind extends BaseGameController
{
    const DRUPAL_KEY = 'skywind';

    /**
     * Get Lobby URL
     */
    protected function getGameLobby(Request $request, Response $response)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct');
        $version = $request->getParam('version', 'v1');

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_sw', [
                'options' => [
                    'languageCode' => $languageCode,
                    'providerProduct' => $providerProduct,
                    'version' => $version,
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
     * Get Lobby URL
     */
    protected function getGameUrl(Request $request, Response $response, $args)
    {
        try {
            $gameCode = $args['gameid'] ?? false;
            $languageCode = $request->getParam('languageCode') ?? 'en';
            $playMode = $request->getParam('playMode') ?? 'true';
            $options['languageCode'] = $languageCode;
            $options['playMode']  = $playMode;

            if (!is_numeric($gameCode)) {
                $options['gameName'] = $gameCode;
            }

            // Check for game code
            if (!$gameCode) {
                throw new \Exception('GameCode is invalid');
            }

            $responseData = $this->get('game_provider_fetcher')->getGameUrlById('icore_sw', $gameCode, [
                'options' => $options
            ]);

            // Check for URL presence
            if (!isset($responseData['url'])) {
                throw new \Exception('No url from response');
            }

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
