<?php

namespace App\Controller;

use App\BaseController;

class UserDetailsController extends BaseController
{
    /**
     * Exposes the current user login state
     */
    public function getLogin($request, $response)
    {
        $data = [];

        try {
            $data['login'] = $this->get('player_session')->isLogin();
        } catch (\Exception $e) {
            // do nothing
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Exposes user specific details
     */
    public function getDetails($request, $response)
    {
        $data = [];

        try {
            $data = $this->get('user_fetcher')->getPlayerDetails();
        } catch (\Exception $e) {
            // do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
