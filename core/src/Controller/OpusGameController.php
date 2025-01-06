<?php

namespace App\Controller;

use App\Drupal\Config;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Symfony\Component\HttpFoundation\Request;
use Slim\Exception\NotFoundException;

use App\BaseController;
use App\Utils\IP;
use App\Utils\Host;

class OpusGameController extends BaseController
{
    const ISSUER = 'webcomposer';
    const AUDIENCE = 'keno';

    /**
     * Exposed Keno game launch cookie
     */
    public function createCookie($request, $response)
    {
        try {
            $config = $this->get('config_fetcher')->getGeneralConfigById('games_opus_provider');
            $language = $this->get('lang');
            $isLogin = $this->get('player_session')->isLogin();
            $data['opus'] = $config;
            $sessiontokenizer = null;
            $gameUri = $data['opus']['opus_alternative_game_url'];

            if ($this->get('player_session')->isLogin()) {
                $secureToken = $this->get('session_fetcher')->getAuthToken();
                $sessiontokenizer = $secureToken['token'];
            }

            $languageParse = Config::parse($config['languages'] ?? '');
            $languageCode = $languageParse[$language] ?? "en-us";

            $options = [
                'issuer' => self::ISSUER,
                'audience' => self::AUDIENCE,
                'expire_time' => time() + 3600,
            ];

            $payload = [
                'token' => $sessiontokenizer,
                'languagecode' => $languageCode,
            ];

            $token = $this->get('jwt_encryption')->encrypt($payload, $options);
            $product = $this->product;
            $redirectUrl = $gameUri . $language. "/$product/game/opus/redirect?token=$token&login=$isLogin";

            return $response->withRedirect($redirectUrl);
        } catch (\Exception $e) {
            // do nothing
        }

        throw new NotFoundException($request, $response);
    }

    /**
     *
     */
    public function showLoader($request, $response)
    {
          $data['gameLoad'] = $this->get('config_fetcher')->getConfigById('games_loading_configuration');

        if ($data['gameLoad']) {
            return $this->view->render($response, '@site/components/loader.html.twig', $data);
        } else {
            $config = $this->get('config_fetcher')->getGeneralConfigById('games_opus_provider');
            $data['games_opus_provider'] = $config;
            $data['content'] = $config['opus_game_loader_content'];
            return $this->view->render($response, '@base/loader.html.twig', $data);
        }
    }

    /**
     * Redirect to Game Lobby Url for opus
     */
    public function createRedirect($request, $response, $args)
    {
        $token = $request->getParam('token');
        $login = $request->getParam('login');

        $config = $this->get('config_fetcher')->getGeneralConfigById('games_opus_provider');
        $data['games_opus_provider'] = $config;
        $domain = Host::getDomain();

        if ($token) {
            try {
                $options = [
                    'issuer' => self::ISSUER,
                    'audience' => self::AUDIENCE,
                ];

                $decrypt = $this->get('jwt_encryption')->decrypt($token, $options);

                $token = $decrypt['token'];
                $languageCode = $decrypt['languagecode'];

                if ($login) {
                    $response = FigResponseCookies::set(
                        $response,
                        SetCookie::create('s')
                                ->withValue($token)
                                ->withdomain('.' . $domain)
                                ->withPath('/')
                    );

                    $response = $response->withRedirect($data['games_opus_provider']['opus_game_url']);
                } else {
                    // Removing Cookie
                    $response = FigResponseCookies::set(
                        $response,
                        SetCookie::create('s')
                            ->withValue('')
                            ->withdomain('.' . $domain)
                            ->withPath('/')
                            ->withExpires(time()-3600)
                    );

                    $response = $response->withRedirect($data['games_opus_provider']['opus_game_free_play_url']);
                }

                $response = FigResponseCookies::set(
                    $response,
                    SetCookie::create('selectedLanguage')
                            ->withValue($languageCode)
                            ->withdomain('.' . $domain)
                            ->withPath('/')
                );
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            // Removing Cookies
            $response = FigResponseCookies::set(
                $response,
                SetCookie::create('s')
                            ->withValue('')
                            ->withdomain('.' . $domain)
                            ->withPath('/')
                            ->withExpires(time()-3600)
            );

            $response = FigResponseCookies::set(
                $response,
                SetCookie::create('selectedLanguage')
                            ->withValue('')
                            ->withdomain('.' . $domain)
                            ->withPath('/')
                            ->withExpires(time()-3600)
            );
        }

        return $response;
    }
}
