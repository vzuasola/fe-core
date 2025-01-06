<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use App\Drupal\Config;
use Slim\Http\Request;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class Playtech extends BaseGameController
{
    const DRUPAL_KEY = 'pas';

    /**
     * Fetch game URL parameters to be used for generating game URL
     */
    protected function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameUrlParams'] = [];
        try {
            $languageCode = $request->getParam('languageCode');
            $playMode = $request->getParam('playMode');

            $this->provider = $this->get('game_provider_fetcher');
            $details = $this->get('player_session')->getDetails();

            // Setup params
            $options['languageCode'] = $languageCode;
            $options['playMode']  = $playMode;
            if (!is_numeric($args['gameid'])) {
                $options['gameName'] = $args['gameid'];
            }

            $responseData = $this->provider->getGameUrlById('icore_pt', $args['gameid'], [
                'options' => $options
            ]);

            if ($responseData['url'] && $details) {
                $data['gameUrlParams'] = $this->extractUrlParams($responseData['url'], $details);
                $data['gameUrlParams'] = $this->addUglReplacements($data['gameUrlParams'], $languageCode, $args['gameid']);
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * The JS code for Playtech expects a different data structure in the response than the rest of providers.
     * So, we need to override the BaseGameController.
     */
    protected function getGameUrlByPlayerGame(Request $request, Response $response, $args)
    {
        $extGameId = $args['gameid'] ?? false;
        // for live-dealer
        $overrideExtGameId = $request->getParam('extGameId', '');
        if ($overrideExtGameId) {
            $extGameId = $overrideExtGameId;
        }
        $portalName = $request->getParam('providerProduct', null) ?? $this->get('settings')['product'];

        $data[] = [];
        $extraParams = $this->getPlayerGameExtraParams($request, $args);
        $languageCode = $request->getParam('languageCode', '');
        $options['options'] = [
            'languageCode' => $languageCode,
            'playMode' => true
        ];

        if (count($extraParams)) {
            $options['options']['properties'] = $extraParams;
        }

        try {
            $gameUrlFetcher = $this->get('player_game_fetcher');
            $responseData =  $gameUrlFetcher->getGameUrlByExtGameId(
                $portalName,
                $extGameId,
                $options
            );
            // Check for URL presence
            if (!isset($responseData['body']['url'])) {
                // Raise exception
                throw new \Exception('No url from response');
            }

            $details = $this->get('player_session')->getDetails();
            if ($details) {
                $data['gameUrlParams'] = $this->extractUrlParams($responseData['body']['url'], $details);
                $data['gameUrlParams'] = $this->addUglReplacements($data['gameUrlParams'], $languageCode, $extGameId);
            } else {
                $data['gameUrlParams'] = [];
            }
        } catch (\Exception $e) {
            $data['gameUrlParams'] = [];
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

    private function extractUrlParams($responseUrl, $details)
    {
        $params = [];
        parse_str(parse_url($responseUrl)['query'], $params);
        // Force username to uppercase to ensure PAS login success
        $params['Username'] = strtoupper($details['username']);
        $params['PlayerId'] = $details['playerId'];
        $params['SecureToken'] = strtoupper(str_replace('-', '', $params['SecureToken']));

        return $params;
    }

    private function addUglReplacements($params, $languageCode, $gameCode)
    {
        $config_fetcher = $this->get('config_fetcher');
        $playtechConfig = $config_fetcher->getGeneralConfigById('games_playtech_provider');

        // UGL is disabled in Drupal CMS — skip the rest
        if (empty($playtechConfig['ugl_switch'])) {
            return $params;
        }
        
        // UGL is enabled — add provided parameters
        $uglLanguages = Config::parse($playtechConfig['ugl_languages']) ?? [];
        $uglUrl = (string) $playtechConfig['ugl_url'];
        $uglCurrency = array_map('trim', explode("\n", $playtechConfig['ugl_currency'] ?? []));
        $uglParameters = Config::parse($playtechConfig['ugl_parameters']) ?? [];

        $search = [
            '{username}', '{gameCodeName}', '{ptLanguage}', '{externalToken}'
        ];

        $replacements = [
            $params['Username'],
            $gameCode,
            $languageCode,
            $params['SecureToken']
        ];

        foreach ($uglParameters as $key => $value) {
            $params[$key] = str_replace($search, $replacements, $value);
        }
        return $params;
    }
}
