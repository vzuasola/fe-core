<?php

namespace App\Dependencies\Client;

use Interop\Container\ContainerInterface;

use GuzzleHttp\Client;

/**
 * Service provider to provide the Drupal client object
 */
class DrupalClient
{
    const GEOIP_HEADER = 'HTTP_X_CUSTOM_LB_GEOIP_COUNTRY';

    /**
     *
     */
    public function __invoke(ContainerInterface $container)
    {
        $settings = $container->get('settings');
        $statsHandler = $container->get('client_stats');

        $lang = $container->get('lang');
        $product = $container->get('product_default');

        $headers = [
            'Product' => $settings['product'],
            'Language' => $lang,
            'Referer' => (string) $container->get('request')->getUri(),
        ];

        // add prefix only if assets should be prefixed
        if ($settings['asset']['prefixed'] || $settings['asset']['prefixed_drupal']) {
            $headers['X-FE-Base-Uri'] = rtrim("/$lang/$product", "/");

            if (isset($settings['asset']['custom_drupal_prefix'])) {
                $headers['X-FE-Base-Uri'] = $settings['asset']['custom_drupal_prefix'];
            }
        }

        // Add geoip of header if set.
        if (isset($_SERVER[self::GEOIP_HEADER])) {
            $headers['X-FE-Country-Code'] = $_SERVER[self::GEOIP_HEADER];
        }

        return new Client([
            'headers' => $headers,
            'on_stats' => function ($stats) use ($statsHandler) {
                $statsHandler->record($stats);
            }
        ]);
    }
}
