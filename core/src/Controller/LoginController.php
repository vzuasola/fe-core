<?php

namespace App\Controller;

use App\BaseController;
use App\Exception\ResponseException;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use App\Form\LoginForm;
use App\Utils\Url;

class LoginController extends BaseController
{
    /**
     *
     */
    public function login($request, $response, $args)
    {
        $formManager = $this->get('form_manager');

        return $formManager->handleSubmission($response, LoginForm::class);
    }

    public function isLoggedIn($request, $response, $args)
    {
        $playerSession = $this->get('player_session');
        $loginStatus = $playerSession->isLogin();

        return $this->get('rest')->output($response, ['active' => $loginStatus]);
    }

    /**
     *
     */
    public function logout($request, $response, $args)
    {
        $this->get('player_session')->logout();

        $handler = $this->get('handler')->getEvent('logout');

        return $handler($request, $response);
    }

    /**
     * Login Page
     */
    public function loginPage($request, $response)
    {
        $player = $this->get('player_session');
        $redirect = $request->getParam('redirect') ?? '/';

        if ($player->isLogin()) {
            return $response->withStatus(302)->withHeader('Location', $redirect);
        }

        $data['header'] = $this->getSection('header_async')->resolve();
        return $this->view->render($response, '@base/components/login/login-page.html.twig', $data);
    }

    /**
     *
     */
    public function renew($request, $response)
    {
        $data['success'] = false;
        if ($this->get('player_session')->refreshToken()) {
            $data['success'] = true;
        }

        return $this->get('rest')->output($response, $data);
    }
}
