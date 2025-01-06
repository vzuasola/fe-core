<?php

namespace App\Controller;

use App\BaseController;
use App\Utils\Url;

use App\Plugins\Integration\Exception\AccountLockedException;
use App\Plugins\Integration\Exception\AccountSuspendedException;

class LegacyAuthController extends BaseController
{
    /**
     * Login user by token this will be deprecated in the future
     */
    public function auth($request, $response, $args)
    {
        $token = $request->getParam('token');
        $redirectTo = Url::generateFromRequest($request, $request->getParam('redirect_to'));
        $success = false;

        try {
            $success = $this->get('player_session')->authenticateByToken($token);
        } catch (\Exception $e) {
            if ($e instanceof AccountLockedException) {
                $handler = $this->handler->getEvent('account_locked');
                return $handler($this->request, $response, null, $redirectTo);
            }

            if ($e instanceof AccountSuspendedException) {
                $handler = $this->handler->getEvent('account_suspended');
                return $handler($this->request, $response, null, $redirectTo);
            }

            if ($e->getCode() == 401) {
                $handler = $this->handler->getEvent('login_failed');
                return $handler($this->request, $response, null, $redirectTo);
            }

            if ($e->getCode() == 500) {
                $handler = $this->handler->getEvent('service_not_available');
                return $handler($this->request, $response, null, $redirectTo);
            }
        }

        if ($success) {
            $handler = $this->handler->getEvent('login_success');
            return $handler($this->request, $response, null, null, $redirectTo);
        }

        $handler = $this->handler->getEvent('login_failed');
        return $handler($this->request, $response, null, $redirectTo);
    }
}
