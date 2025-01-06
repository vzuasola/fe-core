<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class AnnouncementLightbox extends CommonSectionBase implements AsyncSectionInterface
{
    /**
     *
     */
    public function processDefinition($data, array $options)
    {
        $result = [];
        $data = parent::processDefinition($data, $options);

        if (isset($data['base']['announcement_lightbox']['base'])) {
            $result = $data['base']['announcement_lightbox']['base'];
        }

        return $result;
    }
}
