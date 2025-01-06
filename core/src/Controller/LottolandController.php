<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Async\Async;
use App\Drupal\Config as Parser;

class LottolandController extends BaseController
{
    /**
     * Get Lobby URL for Lottoland
     */
    public function getLottolandGameLobby($request, $response, $args)
    {
        $definitions['gameConfig'] = $this->get('config_fetcher_async')->getConfigById('games_configuration');
        $definitions['header'] = $this->getSection('header_common');
        $definitions['session'] = $this->getSection('session_timeout_common');
        $definitions['icore'] = $this->get('config_fetcher')->getGeneralConfigById('icore_games_integration');

        $data = Async::resolve($definitions);

        $data['languageSwitcherDisabled'] = true;
        $data['headerNotificationDisabled'] = true;
        $data['title'] = 'Game Page';
        $language = $this->get('lang');
        $languages = $data['icore']['lottoland_language_mapping'];

        $result = Parser::parse($languages);
        $languageCode = $result[$language];

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getLobby('icore_lottoland', [
                'options' => [
                    'languageCode' => $languageCode
                ]
            ]);

            if ($responseData) {
                $lottolandConfig = parse_url($responseData, PHP_URL_QUERY);
                parse_str($lottolandConfig, $data['lottolandConfig']);
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->view->render($response, '@base/components/games-iframe.html.twig', $data);
    }
}
