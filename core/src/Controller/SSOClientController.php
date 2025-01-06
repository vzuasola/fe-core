<?php

namespace App\Controller;

use App\BaseController;
use App\SSO\SSOInterface;

use Dflydev\FigCookies\FigResponseCookies;

class SSOClientController extends BaseController
{
    /**
     * Validates the session ID
     *
     * Checks if the current session token is valid, this is being called by AJAX
     */
    public function validateSessionId($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $id = $body['id'];

        $isClientAuthenticated = $this->get('session_sso')->isClientAuthenticated($id);

        if ($isClientAuthenticated) {
            $this->get('session_sso')->setClientIdentifier($id);
            $data['status'] = 200;
        } else {
            $data['status'] = 100;
        }

        return $this->get('rest')->output($response, $data);
    }
}
