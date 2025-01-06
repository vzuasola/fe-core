<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

use Firebase\JWT\JWT;
use App\Utils\Url;

class LivechatAsync implements AsyncSectionInterface
{
    const JWT_KEY = 'secret-jwt-key';
    const TIMEOUT = 1800;

    /**
     * The service container
     */
    private $container;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->config = $container->get('config_fetcher_async');
        $this->request = $container->get('request');
        $this->player = $container->get('player_session');
        $this->scripts = $container->get('scripts');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'avaya' => $this->config->getGeneralConfigById('avaya_configuration'),
        ];
    }

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function processDefinition($data, array $options)
    {
        $isLogin = $this->player->isLogin();
        $data['avaya']['base_url'] = Url::generateFromRequest(
            $this->request,
            $data['avaya']['base_url']
        ) ?? 'https://www.cs-livechat.com/c/en/mc-desktop?brand=Dafa&height=760&width=360&title=avayaChatWindow';
        $data['avaya']['url_post'] = Url::generateFromRequest(
            $this->request,
            $data['avaya']['url_post']
        ) ?? 'https://www.cs-livechat.com/s/jwt';
        $data['avaya']['xdomain_proxy'] = Url::generateFromRequest(
            $this->request,
            $data['avaya']['xdomain_proxy']
        ) ?? 'https://www.cs-livechat.com';

        $data['avaya']['bearer_token'] = $data['avaya']['bearer_token'] ?? '';

        if ($isLogin) {
            $data['player'] = $this->player->getDetails();

            $validityTime = $data['avaya']['validity_time'] ?? self::TIMEOUT;

            $user = [
                'username' => $data['player']['username'],
                'email' => $data['player']['email'],
                'level' => 'Reg',
                'exp' => strtotime('+' . $validityTime . ' seconds'),
            ];

            $key = $data['avaya']['jwt_key'] ?? self::JWT_KEY;

            $data['avaya']['validity_time'] = $user['exp'];
            $data['avaya']['jwt_token'] = JWT::encode($user, $key, 'HS256');
        }

        $this->scripts->attach([
            'liveChatConfig' => $data
        ], $options);

        return $data;
    }
}
