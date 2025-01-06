<?php

namespace App\Controller;

use App\BaseController;
use App\Utils\Host;

class SSOServerController extends BaseController
{
    /**
     * Gets the XDM endpoint
     *
     * Exposes the current server ID to the whitelisted clients
     */
    public function getXDM($request, $response, $args)
    {
        $sessionId = $this->get('session_sso')->getServerIdentifier();

        $data['session'] = [
            'id' => $sessionId,
        ];

        $response = $this->view->render($response, '@base/components/sso/xdm.html.twig', $data);

        $domain = $request->getQueryParam('xdm_e');
        $domain = Host::getHostnameFromUri($domain);

        if ($this->get('session_sso')->isDomainAllowed($domain)) {
            $response = $response->withoutHeader('X-Frame-Options');
        }

        return $response;
    }

    /**
     * Handles the server to client redirection
     */
    public function doRedirect($request, $response, $args)
    {
        $scheme = $request->getQueryParam('sso-scheme');
        $path = $request->getQueryParam('sso-path');

        $query = $request->getQueryParams();

        // remove sso related query parameters used by the server
        unset($query['sso-scheme']);
        unset($query['sso-path']);

        $domain = Host::getHostnameFromUri($path);

        // add the tokenize SSO
        if ($this->get('session_sso')->isDomainAllowed($domain)) {
            $query['sso-token'] = $this->get('session_sso')->getServerIdentifier();
        } else {
            $query['sso-token'] = 'none';
        }

        $queryString = http_build_query($query);
        $redirect = "$scheme://$path";

        if (!empty($queryString)) {
            $redirect = "$redirect?$queryString";
        }

        return $response->withStatus(302)->withHeader('Location', $redirect);
    }
}
