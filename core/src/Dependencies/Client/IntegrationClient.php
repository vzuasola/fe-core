<?php

namespace App\Dependencies\Client;

use Interop\Container\ContainerInterface;

use GuzzleHttp\Client;
use App\Utils\IP;

/**
 * Service provider to provide the Integration client object
 */
class IntegrationClient
{
    /**
     *
     */
    public function __invoke(ContainerInterface $container)
    {
        $settings = $container->get('settings');
        $statsHandler = $container->get('client_stats');

        $headers = [
            'Product' => $settings['product'],
            'IP' => IP::getIpAddress(),
            'Referer' => (string) $container->get('request')->getUri(),
        ];

        // Add dafa connect on the header if it is supported
        if ($settings['dafaconnect']['enable'] &&
            isset($_SERVER['HTTP_USER_AGENT']) &&
            preg_match('/DafaConnect/i', $_SERVER['HTTP_USER_AGENT'])
        ) {
            $headers['Dafa-Connect'] = 'true';
        }

        return new Client([
            'headers' => $headers,
            'on_stats' => function ($stats) use ($statsHandler) {
                $statsHandler->record($stats);
            }
        ]);
    }
}
