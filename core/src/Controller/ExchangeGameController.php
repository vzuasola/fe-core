<?php

namespace App\Controller;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Symfony\Component\HttpFoundation\Request;
use Slim\Exception\NotFoundException;

use App\BaseController;
use App\Utils\IP;
use App\Utils\Host;
use App\Drupal\Config;

class ExchangeGameController extends BaseController
{
    /**
     * Exposed Exchange game launch cookie
     */
    public function cookieCreate($request, $response)
    {
        try {
            if ($this->get('player_session')->isLogin()) {
                $config = $this->get('config_fetcher')->getGeneralConfigById('games_exchange_provider');
                $language = $this->get('lang');
                $secureToken = $this->get('session_fetcher')->getAuthToken();

                $providerMapping = Config::parse($config['languages'] ?? '');
                $languageCode = $providerMapping[$language];
                $sessiontokenizer = $secureToken['token'];

                $domain = Host::getDomain();
                $subdomain = $config['transaction_subdomain'];
                $gamePath = $config['exchange_game_url'];
                $gameUri = "$subdomain.$domain/$gamePath";

                if ($this->isMobile()) {
                    $configParam = $config['tablet_game_url'];
                    $param = $this->get('token_parser')->processTokens($configParam);
                    $tabletSubdomain = $config['exchange_tablet_url'];
                    $gameUri = "$tabletSubdomain.$domain/$param";
                }

                $response = FigResponseCookies::set(
                    $response,
                    SetCookie::create('g')
                            ->withValue($sessiontokenizer)
                            ->withdomain('.' . $domain)
                            ->withPath('/')
                );

                $response = FigResponseCookies::set(
                    $response,
                    SetCookie::create('lang')
                            ->withValue($languageCode)
                            ->withdomain('.' . $domain)
                            ->withPath('/')
                );
                $response = $response->withRedirect($gameUri);
            }
            return $response;
        } catch (\Exception $e) {
            // do nothing
        }

        throw new NotFoundException($request, $response);
    }

    public function isMobile()
    {
        return $this->request->getHeaderLine('X-Custom-Device-View') === 'mobile';
    }
}
