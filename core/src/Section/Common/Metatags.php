<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class Metatags extends CommonSectionBase implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);

        $this->blockUtils = $container->get('block_utils');
    }

    /**
     *
     */
    public function processDefinition($data, array $options)
    {
        $result = [];
        $data = parent::processDefinition($data, $options);

        if (isset($data['base']['metatags'])) {
            $result = $this->getSectionData($data['base']['metatags'], $options);
        }

        return $result;
    }

    /**
     *
     */
    protected function getSectionData($data, array $options)
    {
        $result = [];
        $metadata = $data['base'];

        if (!empty($metadata)) {
            foreach ($metadata as $listing) {
                $visibilty = $listing['field_per_page_visibility'][0]['value'] ?? 0;

                if ($this->blockUtils->isVisibleOn($visibilty)) {
                    $result[] = $listing;
                }
            }
        }

        return $result;
    }
}
