<?php

namespace App\Controller;

use App\BaseController;
use App\Utils\IP;
use App\Utils\Host;

class DefaultController extends BaseController
{
    /**
     * Default home page
     */
    public function index($request, $response, $args)
    {
        $this->logger->info("Slim '/' route");

        return $this->view->render($response, '@base/index.html.twig');
    }

    /**
     * Debug sample
     */
    public function debug($request, $response, $args)
    {
        d(Host::getHostname());
        d(Host::getDomain());
        d(IP::getIpAddress());
        d(ini_get_all());
        d($_SESSION);
        d($_SERVER);
        d($this->get('secure_session')->get('player.detail'));
    }
}
