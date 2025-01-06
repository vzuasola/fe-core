<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;

class RegistrationController extends BaseController
{
    /**
     * Make Deposit
     */
    public function makeDeposit($request, $response, $args)
    {
        $data = [];

        $data['header'] = $this->getSection('header');
        $data['footer'] = $this->getSection('footer');
        $data['session'] = $this->getSection('session_timeout');
        $data['floating_banner'] = $this->getSection('floating_banner');
        $data['outdated_browser'] = $this->getSection('legacy_browser');
        $data['announcement_lightbox'] = $this->getSection('announcement_lightbox');
        $data['livechat'] = $this->getSection('livechat');
        $data['downloadable'] = $this->getSection('downloadable');

        try {
            $config = $this->get('config_fetcher')->getGeneralConfigById('registration_landing_page_configuration');

            $data['makeDeposit'] = $config['registration_content']['value'] ?? null;
            $data['title'] = $config['registration_title'] ?? null;
        } catch (\Exception $e) {
            // do nothing
        }

        if (!isset($data['makeDeposit']) || ! $this->get('player_session')->isLogin()) {
            throw new NotFoundException($request, $response);
        }

        return $this->view->render($response, '@base/registration/registration-step2.html.twig', $data);
    }
}
