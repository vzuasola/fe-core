<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Slim\Exception\NotFoundException;

use App\BaseController;
use App\Utils\IP;
use App\Utils\Host;
use App\Drupal\Config;

class GpiGameController extends BaseController
{
    const LIVE_DEALER_PRODUCT_ID = 1035;
    const ARCADE_PRODUCT_ID = 1037;

    /**
     * Exposed Gpi Keno game launch cookie
     */
    public function createKenoGameLobby($request, $response)
    {
        try {
            if ($this->get('player_session')->isLogin()) {
                $gpiconfig =  $this->get('config_fetcher')->getGeneralConfigById('games_gpi_provider');

                $language = $this->get('lang');
                $secureToken = $this->get('session_fetcher')->getAuthToken();

                $providerMapping = Config::parse($gpiconfig['gpi_keno_language_mapping'] ?? '');
                $languageCode = $providerMapping[$language];
                $sessiontokenizer = $secureToken['token'];
                $domain = $gpiconfig['gpi_game_url'];
                $versionno = $gpiconfig['gpi_lottery_keno_version_no'];
                $vendor = $gpiconfig['gpi_vendor_id'];
                $ticket = $sessiontokenizer.'.1036';
                $extraParams = Config::parse($gpiconfig['gpi_keno_extra_params']);
                $args = array_merge([
                   'lang' => $languageCode,
                   'version' => $versionno,
                   'vendor' => $vendor,
                   'ticket' => $ticket,
                ], $extraParams);
                $query = http_build_query($args);

                $gameUri = "$domain?$query";
                $response = $response->withRedirect($gameUri);
            }
              return $response;
        } catch (\Exception $e) {
            // do nothing
        }

        throw new NotFoundException($request, $response);
    }

    /**
     * Exposed Gpi Pk10 game launch
     */
    public function createPkGameLobby($request, $response)
    {
        try {
            if ($this->get('player_session')->isLogin()) {
                $gpiconfig =  $this->get('config_fetcher')->getGeneralConfigById('games_gpi_provider');

                $language = $this->get('lang');
                $secureToken = $this->get('session_fetcher')->getAuthToken();

                $providerMapping = Config::parse($gpiconfig['gpi_pk10_language_mapping'] ?? '');
                $languageCode = $providerMapping[$language];
                $sessiontokenizer = $secureToken['token'];
                $domain = $gpiconfig['gpi_game_url'];
                $game = 'pk10';
                $vendor = $gpiconfig['gpi_vendor_id'];
                $ticket = $sessiontokenizer.'.1036';
                $extraParams = Config::parse($gpiconfig['gpi_pk10_extra_params']);
                $args = array_merge([
                   'lang' => $languageCode,
                   'vendor' => $vendor,
                   'game' => $game,
                   'ticket' => $ticket,
                ], $extraParams);

                $query = http_build_query($args);
                $gameUri = "$domain?$query";
                $response = $response->withRedirect($gameUri);
            }
              return $response;
        } catch (\Exception $e) {
            // do nothing
        }

        throw new NotFoundException($request, $response);
    }

    /**
     * Exposed GPi Thai Game Lobby
     */
    public function createThaiGameLobby($request, $response)
    {
        try {
            if ($this->get('player_session')->isLogin()) {
                $gpiconfig =  $this->get('config_fetcher')->getGeneralConfigById('games_gpi_provider');

                $language = $this->get('lang');
                $secureToken = $this->get('session_fetcher')->getAuthToken();

                $providerMapping = Config::parse($gpiconfig['gpi_thai_lottey_language_mapping'] ?? '');
                $languageCode = $providerMapping[$language];
                $sessiontokenizer = $secureToken['token'];
                $domain = $gpiconfig['gpi_game_url'];
                $game = 'thailottery';
                $vendor = $gpiconfig['gpi_vendor_id'];
                $ticket = $sessiontokenizer.'.1036';
                $extraParams = Config::parse($gpiconfig['gpi_thai_lottey_extra_params']);
                $args = array_merge([
                   'lang' => $languageCode,
                   'vendor' => $vendor,
                   'game' => $game,
                   'ticket' => $ticket,
                ], $extraParams);
                $query = http_build_query($args);
                $gameUri = "$domain?$query";
                $response = $response->withRedirect($gameUri);
            }
              return $response;
        } catch (\Exception $e) {
            // do nothing
        }

        throw new NotFoundException($request, $response);
    }

    /**
     * Exposed GPI Live Dealer Game Lobby
     */
    public function createLiveDealerLobby($request, $response)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');

        try {
            if ($this->get('player_session')->isLogin()) {
                $gpiconfig =  $this->get('config_fetcher')->getGeneralConfigById('games_gpi_provider');
                $secureToken = $this->get('session_fetcher')->getAuthToken();
                $sessiontokenizer = $secureToken['token'];
                $domain = $gpiconfig['gpi_game_url'];
                $vendor = $gpiconfig['gpi_vendor_id'];
                $extraParams = Config::parse($gpiconfig['gpi_live_dealer_extra_params']);
                $ticket = sprintf('%s.%d', $sessiontokenizer, SELF::LIVE_DEALER_PRODUCT_ID);
                $args = array_merge([
                   'lang' => $languageCode,
                   'op' => $vendor,
                   'token' => $ticket,
                   'sys' => 'CUSTOM',
                ], $extraParams);
                $query = http_build_query($args);
                $gameUri = "$domain?$query";
                $data['gameurl'] = $gameUri;

                \App\Kernel::logger('workflow')->info('GL', [
                    'status_code' => 'OK',
                    'request' => (string)$request->getUri(),
                    'response' => array_merge($args, [
                        'domain' => $domain
                    ]),
                    'game_url' => $gameUri
                ]);
            }
        } catch (\Exception $e) {
            \App\Kernel::logger('workflow')->info('GL', [
                'status_code' => 'NOT OK',
                'request' => (string)$request->getUri(),
                'others' => [
                    'exception' => $e->getMessage()
                ]
            ]);
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Generates gpi arcade game url
     */
    public function getArcadeGameUrl($request, $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $gameCode = $args['gameid'];

        try {
            if ($this->get('player_session')->isLogin()) {
                $gpiconfig =  $this->get('config_fetcher')->getGeneralConfigById('games_gpi_provider');
                $secureToken = $this->get('session_fetcher')->getAuthToken();
                $sessiontokenizer = $secureToken['token'];
                $domain = $gpiconfig['gpi_game_url'];
                $vendor = $gpiconfig['gpi_vendor_id'];
                $extraParams = Config::parse($gpiconfig['gpi_ladder_extra_params']);
                $ticket = sprintf('%s.%d', $sessiontokenizer, SELF::ARCADE_PRODUCT_ID);
                $args = array_merge([
                   'lang' => $languageCode,
                   'vendor' => $vendor,
                   'game' => $gameCode,
                   'ticket' => $ticket
                ], $extraParams);
                $query = http_build_query($args);
                $gameUri = "$domain?$query";
                $data['gameurl'] = $gameUri;

                \App\Kernel::logger('workflow')->info('GL', [
                    'status_code' => 'OK',
                    'request' => (string)$request->getUri(),
                    'response' => array_merge($args, [
                        'domain' => $domain
                    ]),
                    'game_url' => $gameUri
                ]);
            }
        } catch (\Exception $e) {
            \App\Kernel::logger('workflow')->info('GL', [
                'status_code' => 'NOT OK',
                'request' => (string)$request->getUri(),
                'others' => [
                    'exception' => $e->getMessage()
                ]
            ]);
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Exposed Gpi Sode game launch cookie
     */
    public function createSodeGameLobby($request, $response)
    {
        try {
            if ($this->get('player_session')->isLogin()) {
                $gpiconfig =  $this->get('config_fetcher')->getGeneralConfigById('games_gpi_provider');

                $language = $this->get('lang');
                $secureToken = $this->get('session_fetcher')->getAuthToken();

                $providerMapping = Config::parse($gpiconfig['gpi_sode_language_mapping'] ?? '');
                $languageCode = $providerMapping[$language];
                $sessiontokenizer = $secureToken['token'];
                $domain = $gpiconfig['gpi_game_url'];
                $versionno = $gpiconfig['gpi_lottery_keno_version_no'];
                $vendor = $gpiconfig['gpi_vendor_id'];
                $ticket = $sessiontokenizer.'.1036';
                $extraParams = Config::parse($gpiconfig['gpi_sode_extra_params']);
                $args = array_merge([
                   'lang' => $languageCode,
                   'version' => $versionno,
                   'vendor' => $vendor,
                   'ticket' => $ticket,
                ], $extraParams);
                $query = http_build_query($args);

                $gameUri = "$domain?$query";
                $response = $response->withRedirect($gameUri);
            }
              return $response;
        } catch (\Exception $e) {
            // do nothing
        }

        throw new NotFoundException($request, $response);
    }
}
