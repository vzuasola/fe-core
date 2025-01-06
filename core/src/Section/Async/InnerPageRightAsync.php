<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class InnerPageRightAsync implements AsyncSectionInterface
{
    /**
     * The views fetcher
     *
     * @var object
     */
    private $views;

    /**
     * The config fetcher object
     *
     * @var object
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
            'base' => $this->views->getViewById('inner_page_right_side_block'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        try {
            $innerPageRightConfig = $data['base'];

            foreach ($innerPageRightConfig as $field) {
                $visibilty = $field['field_exclude_these_pages'][0]['value'] ?? 0;

                if (empty($visibilty) || !$this->blockUtils->isVisibleOn($visibilty)) {
                    $item = $field;

                    $item['field_innerpage_right_side_block'] = array_filter(
                        $field['field_innerpage_right_side_block'],
                        function ($value) {
                            return $value['field_enable'][0]['value'] ?? false;
                        }
                    );

                    $result[] = $item;
                }
            }
        } catch (\Exception $e) {
            $result = [];
        }
        return $result;
    }
}
