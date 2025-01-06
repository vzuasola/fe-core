<?php

namespace App\GameProvider\Playtech;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\GameProvider\GameProviderInterface;
use App\Drupal\Config;

/**
 * Playtech provider setting
 */
class Provider implements GameProviderInterface
{
    /**
     * The playtech provider ID
     */
    const KEY = 'playtech';

    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->config = $container->get('config_fetcher');
        $this->provider = $container->get('game_provider_fetcher');
        $this->scripts = $container->get('scripts');
        $this->session = $container->get('player_session');
    }

    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $config = $this->config->getGeneralConfigById('games_playtech_provider');
            $data = [
                'language_map'=> Config::parse($config['languages'] ?? ''),
                'iapiconf_override' => Config::parse($config['iapiconf_override'] ?? ''),
                'error_handling' => []
            ];

            if (isset($config['error_header_title_text']) && !empty($config['error_header_title_text'])) {
                $data['error_handling']['headerTitle'] = $config['error_header_title_text'];
            }

            if (isset($config['error_mapping']) && !empty($config['error_mapping'])) {
                $map = array_map(function ($value) {
                    $val = explode("|", trim($value));
                    if ($val[0]) {
                        return [
                            'code' => $val[0],
                            'message' => $val[1] ?? '',
                            'header' => $val[2] ?? false
                        ];
                    }
                }, explode(PHP_EOL, $config['error_mapping']));

                // Convert mapping structure
                foreach ($map as $value) {
                    $data['error_handling']['mapping'][$value['code']] = $value;
                }
            }

            if (isset($config['error_button']) && !empty($config['error_button'])) {
                list($data['error_handling']['button']['text'], $data['error_handling']['button']['url']) = explode(
                    "|",
                    $config['error_button']
                );
            }

            if ($this->session->isLogin()) {
                $data['player'] = [
                    'username' => strtoupper($this->session->getUsername()),
                    'token' => $this->session->getToken()
                ];
            }

            $this->scripts->attach([
                'pas' => $data
            ]);
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
        return $this->provider->getJavascriptAssets(self::KEY);
    }
}
