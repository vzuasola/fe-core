<?php

namespace App\Monolog\Processors;

use Interop\Container\ContainerInterface;
use App\Utils\IP;
use App\Utils\Host;

/**
 *
 */
class WorkflowProcessor
{
    /**
     *
     */
    public function __construct(ContainerInterface $container)
    {
        $this->playerSession = $container->get('player_session');
        $this->settings = $container->get('settings');
        $this->lang = $container->get('lang');
        $this->request = $container->get('request');
    }

    /**
     *
     */
    public function __invoke(array $record)
    {
        $record['context']['ip'] = IP::getIpAddress();
        $record['context']['timestamp'] = time();
        $record['context']['phpsessid'] = session_id();

        $record['context']['hostname'] = Host::getHostname();
        $record['context']['url'] = $this->request->getUri()->getPath();
        $record['context']['referrer'] = $_SERVER["HTTP_REFERER"] ?? "";

        $record['context']['workflow'] = strtolower($record['message']);

        if (!isset($record['context']['username'])) {
            try {
                $record['context']['username'] = $this->playerSession->getUsername();
            } catch (\Exception $e) {
                // do nothing
            }
        }

        if (!isset($record['context']['product'])) {
            $record['context']['product'] = $this->settings->get('product');
        }

        if (!isset($record['context']['platform'])) {
            $record['context']['platform'] = $this->settings->get('platform');
        }

        if (!isset($record['context']['country_code'])) {
            $record['context']['country_code'] = $_SERVER['HTTP_X_CUSTOM_LB_GEOIP_COUNTRY'] ?? "";
        }

        if (!isset($record['context']['session_guid'])) {
            try {
                $record['context']['session_guid'] = $this->playerSession->getToken();
            } catch (\Exception $e) {
                // do nothing
            }
        }

        if (!isset($record['context']['language'])) {
            $record['context']['language'] = $this->lang;
        }

        // JSONify these records if it exists

        if (!empty($record['context']['request']) && is_array($record['context']['request'])) {
            $record['context']['request'] = json_encode($record['context']['request']);
        }

        if (!empty($record['context']['response']) && is_array($record['context']['response'])) {
            $record['context']['response'] = json_encode($record['context']['response']);
        }

        return $record;
    }
}
