<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class DownloadableAsync implements AsyncSectionInterface
{
    /**-
     * Views Fetcher object
     */
    private $views;

    /**
     * Scripts object
     */
    private $scripts;

    /**
     * Block utility helper
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
        $this->scripts = $container->get('scripts');
        $this->blockUtils = $container->get('block_utils');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'base' => $this->views->getViewById('downloadable'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];
        $files = $data['base'];
        if (!empty($files)) {
            foreach ($files as $file) {
                $visibilty = $file['field_include_in_page'][0]['value'] ?? 0;
                if ($this->blockUtils->isVisibleOn($visibilty)) {
                    if ($file['field_platforms']) {
                        $result[] = [
                            'file' => $file['field_downloadable_file_link'][0]['value'] ?? '',
                            'platforms' => array_column($file['field_platforms'], 'value')
                        ];
                    } else {
                        $result[] = $file['field_downloadable_file_link'][0]['value'] ?? '';
                    }
                }
            }
        }

        // attach scripts to page
        $this->scripts->attach([
            'downloadableFiles' => $result ?? []
        ]);
    }
}
