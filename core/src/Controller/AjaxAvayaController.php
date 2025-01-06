<?php

namespace App\Controller;

use App\BaseController;
use Firebase\JWT\JWT;

class AjaxAvayaController extends BaseController
{
    /**
     * Generate JWT Token
     */
    public function jwt($request, $response, $args)
    {
        $data = [];
        try {
            $isLogin = $this->get('player_session')->isLogin();
        } catch (\Exception $e) {
            $isLogin = false;
        }

        try {
            $avaya = $this->get('config_fetcher')->getGeneralConfigById('avaya_configuration');
            if ($isLogin) {
                $playerInfo = $this->get('player_session')->getDetails();
                $validityTime = $avaya['validity_time'] ?? 1200;
                $userInfo = [
                    'username' => $playerInfo['username'],
                    'email' => $playerInfo['email'],
                    'level' => 'Reg',
                    'exp' => strtotime("+" . $validityTime . " seconds")
                ];

                $data['validity_time'] = $userInfo['exp'];
                $jwt = JWT::encode(
                    $userInfo,
                    $avaya['jwt_key'] ?? '',
                    'HS256',
                    null,
                    null
                );
                $data['vipLevel'] = $playerInfo['vipLevel'] ?? 15; // 15 is the default vip level
            }
            $data['baseUrl'] = $avaya['base_url'] ?? '';
            $data['jwt'] = $jwt ?? false;
        } catch (\Exception $e) {
            $data = [];
        }

        return $this->get('rest')->output($response, $data);
    }
}
