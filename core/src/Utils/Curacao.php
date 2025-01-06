<?php

namespace App\Utils;

use App\Drupal\Config;

class Curacao
{
    /**
     * Returns data from GCB configs to the template.
     * Used for Twig Data
     * @param array $config GCB Config from drupal
     * @return array Formatted config array
     */
    public static function getGCBData(array $config)
    {
        return [
            'gcb_feature_flag' => $config['gcb_feature_flag'] ?? false,
            'gcb_image_url' => $config['gcb_image_url'] ?? '',
        ];
    }

    /**
     * Returns data from GCB configs to the template.
     * Used for js attachments
     * @param array $config GCB Config from drupal
     * @return array Formatted config array
     */
    public static function getGCBAttachmentsData(array $config)
    {
        $domainMapping = Config::parseMultidimensional($config['gcb_domain_mapping'] ?? '');
        return [
            'gcb_link' => $config['gcb_link'] ?? '',
            'gcb_domain_mapping' => $domainMapping,
        ];
    }
}
