<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class MetatagsAsync implements AsyncSectionInterface
{
    /**
     * The views object
     *
     * @var object
     */
    private $views;

    /**
     * Block utility helper
     *
     * @var object
     */
    private $blockUtils;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->views = $container->get('views_fetcher_async');
        $this->blockUtils = $container->get('block_utils');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'base' => $this->views->getViewById('metatag_entity'),
        ];
    }

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function processDefinition($data, array $options)
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
