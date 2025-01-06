<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

use Firebase\JWT\JWT;
use App\Utils\Url;

class Livechat extends CommonSectionBase implements AsyncSectionInterface
{
    const JWT_KEY = 'secret-jwt-key';
    const TIMEOUT = 1800;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);

        $this->request = $container->get('request');
        $this->player = $container->get('player');
        $this->playerSession = $container->get('player_session');
        $this->scripts = $container->get('scripts');
    }

    /**
     *
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        $data = parent::processDefinition($data, $options);

        if (isset($data['base']['livechat'])) {
            $result = $this->getSectionData($data['base']['livechat'], $options);
        }

        return $result;
    }

    /**
     *
     */
    protected function getSectionData($data, array $options)
    {
        $isLogin = $this->playerSession->isLogin();

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
            $validityTime = $data['avaya']['validity_time'] ?? self::TIMEOUT;

            $user = [
                'username' => $this->player->getUsername(),
                'email' => $this->player->getEmail(),
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
