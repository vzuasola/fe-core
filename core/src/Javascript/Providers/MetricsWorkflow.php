<?php

namespace App\Javascript\Providers;

use App\Plugins\Javascript\ScriptProviderInterface;
use App\Monolog\Workflows;

/**
 *
 */
class MetricsWorkflow implements ScriptProviderInterface
{
    const EXCLUDE = [
        'ip',
        'timestamp',
        'phpsessid',
        'hostname',
        'url',
        'referrer',
    ];

    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->playerSession = $container->get('player_session');
        $this->settings = $container->get('settings');
        $this->lang = $container->get('lang');
    }

    /**
     * @{inheritdoc}
     */
    public function getAttachments()
    {
        $data['product'] = $this->settings->get('product');
        $data['platform'] = $this->settings->get('platform');
        $data['country_code'] = $_SERVER['HTTP_X_CUSTOM_LB_GEOIP_COUNTRY'] ?? "";

        try {
            if ($this->playerSession->isLogin()) {
                $data['username'] = $this->playerSession->getUsername();
                $data['session_guid'] = $this->playerSession->getToken();
            }
        } catch (\Exception $e) {
            // do nothing
        }

        $data['language'] = $this->lang;

        return [
            'metrics_log' => [
                'workflows' => array_values(array_diff(Workflows::FIELDS, self::EXCLUDE)) ,
                'data' => $data,
            ],
        ];
    }
}
