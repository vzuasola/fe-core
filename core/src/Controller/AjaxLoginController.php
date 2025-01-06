<?php

namespace App\Controller;

use App\BaseController;
use App\Form\LoginLightboxForm;
use App\Utils\Url;

use App\Plugins\Integration\Exception\AccountLockedException;
use App\Plugins\Integration\Exception\AccountSuspendedException;

class AjaxLoginController extends BaseController
{
    /**
     * Login via AJAX
     */
    public function ajaxLogin($request, $response, $args)
    {
        $formManager = $this->get('form_manager');

        return $formManager->handleSubmission($response, LoginLightboxForm::class);
    }
}
