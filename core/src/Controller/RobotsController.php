<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class RobotsController extends BaseController
{

    /**
     * Get configurations for push notification
     */
    public function getRobotsConfig($request, $response)
    {
        $data = [];

        try {
            $data['robots'] = $this->get('config_fetcher')->getGeneralConfigById('robots_configuration');
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->view->render($response, '@base/components/robots/robots.txt.twig', $data);
    }
}
