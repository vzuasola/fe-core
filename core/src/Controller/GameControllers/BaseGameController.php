<?php

namespace App\Controller\GameControllers;

use App\BaseController;
use App\Drupal\Config;
use App\Slim\Response;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;

/**
 * This controller should be extended by game controllers that want to
 * provide support for direct table launching through PlayerGame API.
 * These controllers should delegate the retrieval of game url to a
 * fetchResource() method using a route like the following:
 *
 * /ajax/game/{resource}/<provider>[/{gameid}]
 *
 * Controller methods for direct table launching or lobby launching through
 * GetGeneralLobbyForProduct endpoint of iCore should be named getGameUrl()
 * and getGameLobby(), respectively.
 *
 * The controller is also expected to override the DRUPAL_KEY constant that defines
 * a provider prefix same as defined in ICORE_GAME_PROVIDERS constant of Drupal's
 * webcomposer_games module for the corresponding provider.
 */
class BaseGameController extends BaseController
{
    const DRUPAL_KEY = '';

    public function fetchResource(Request $request, Response $response, $args)
    {
        $resource = $args['resource'] ?? '';
        $portalName = $this->get('settings')['product'];
        $productConfig = $this->get('config_fetcher')->withProduct($portalName);

        $iCoreGamesConfig = $productConfig->getGeneralConfigById('icore_games_integration');
        $usePlayerGame = $iCoreGamesConfig[static::DRUPAL_KEY . '_use_playergame_api'] ?? false;
        $args['launcherType'] = $iCoreGamesConfig[static::DRUPAL_KEY . '_use_html_param'] ?? false;

        switch(true) {
            case ($usePlayerGame && $resource === 'url'):
                return $this->getGameUrlByPlayerGame($request, $response, $args);
            case ($resource === 'url'):
                return $this->getGameUrl($request, $response, $args);
            case ($resource === 'lobby'):
                return $this->getGameLobby($request, $response);
            default:
                throw new NotFoundException($request, $response);
        }
    }

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
        $options['options'] = [
            'languageCode' => $request->getParam('languageCode', ''),
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

            if (!isset($responseData['body']['url'])) {
                // Raise exception
                throw new \Exception('No url from response');
            }

            $data['gameurl'] = $responseData['body']['url'];
        } catch (\Exception $e) {
            // Set game url
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

    /**
     * This method maps error code returned by PlayerGame API
     * to configured error message based on error code from CMS.
     */
    protected function getGameErrorMessage(Request $request, $responseData)
    {
        $params = $request->getParsedBody();
        $product = $this->get('settings')['product'];
        $conf = $this->get('config_fetcher')->withProduct($product);

        $config = $conf->getConfig('webcomposer_config.playergame_error_handling') ?? [];
        $responseCode = $responseData['responseCode'] ?? 500;
        // Parse
        $playerErrors = Config::parse($config['playergame_error_message'] ?? '') ?? [];
        // Compile & return
        return [
            'errorCode' => $responseCode,
            'errorMessage' => $playerErrors[$responseCode] ?? 'There was an internal error',
            'errorButton' => $config['playergame_error_button'] ?? 'OK',
        ];
    }

    /**
     * This method can be overriden by provider controller if some
     * properties are required for that provider by iCore PlayerGame.
     */
    protected function getPlayerGameExtraParams(Request $request, $args)
    {
        return [];
    }

    /**
     * To be overriden by controllers that support direct table launching.
     */
    protected function getGameUrl(Request $request, Response $response, $args) {
        throw new \Exception("Direct table launching is not supported by this provider.");
    }

    /**
     * To be overriden by controllers that support lobby launching.
     */
    protected function getGameLobby(Request $request, Response $response) {
        throw new \Exception("Lobby launching is not supported by this provider.");
    }
}
