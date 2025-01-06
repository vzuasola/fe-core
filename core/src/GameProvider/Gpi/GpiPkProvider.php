<?php

namespace App\GameProvider\Gpi;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Drupal\Config;

/**
 * Provider Class for GpiPk Gaming
 */
class GpiPkProvider implements GameProviderInterface
{

    /**
     * Sets the container
     */
    const COOKIE = 'currency';

    /**
     * Main domain
     *
     * @var string
    */
    private $domain;

    public function setContainer($container)
    {
        $this->scripts = $container->get('scripts');
        $this->configFetcher = $container->get('config_fetcher');
        $this->playerSession = $container->get('player_session');
        $this->userFetcher = $container->get('user_fetcher');
        $this->domain = $container->get('settings')['session_handler']['cookie_domain'];
        $this->playerInfo = $container->get('player');
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        $isLogin = $this->playerSession->isLogin();
        try {
            $config =  $this->configFetcher->getGeneralConfigById('games_gpi_provider');

            if ($isLogin && $config) {
                $currency = $this->playerInfo->getCurrency();
                setcookie(self::COOKIE, $currency, null, '/', $this->domain);
                $this->scripts->attach([
                    'gpi_pk10' => [
                        'currencies' => $config['gpi_pk10_currency'],
                        'languages' => $config['gpi_pk10_language_mapping']
                    ]
                ]);
            }
             $this->scripts->attach([
                        'game_provider' => [
                            'gpi_pk' => [ 'countries' => $config['gpi_pk10_country']
                            ]
                        ]
                ], true);
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onSessionDestroy()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascriptAssets()
    {
        return [];
    }
}
