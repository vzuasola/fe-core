<?php

namespace App\Section;

use App\Plugins\Section\SectionInterface;
use Interop\Container\ContainerInterface;

class PromotionRight implements SectionInterface
{
    /**
     * The node utility object
     *
     * @var object
     */
    private $nodeUtils;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->nodeUtils = $container->get('node_utils');
    }

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function getSection(array $options)
    {
        $result = [];

        $node = $options['node'];

        $nid = $node['nid'][0]['value'];
        $show = $node['field_right_side_block_option'][0]['value'];

        if ($show) {
            try {
                $promotions = $this->getPromotionSpecificNodes($node);

                $this->removeNodeFromCollection($node, $promotions);
                $this->removeHiddenPromotions($promotions);

                $this->nodeUtils->filterByLoginState($promotions);

                $result['items'] = $promotions;
                $result['show'] = $show;

                // get the title
                $result['title'] = $node['field_right_side_block_title'][0]['value'];
            } catch (\Exception $e) {
                // just left it blank
            }
        }

        return $result;
    }

    /**
     * Get all promotion tagged for the given node
     *
     * @param array $node The node object
     *
     * @return array
     */
    private function getPromotionSpecificNodes($node)
    {
        $promotions = [];
        $promotionList = $node['field_right_side_block_content'] ?? null;

        foreach ($promotionList as $key => $value) {
            $nid = $value['nid'][0]['value'];

            $promotions[$nid] = $value;
        }

        return $promotions;
    }

    /**
     * Get all promotion tagged for the given node
     *
     * @param array $node The node object
     *
     * @return array
     */
    private function removeHiddenPromotions(&$collection)
    {
        $collection = array_filter($collection, function ($value, $key) {
            $isHidden = $value['field_hide_promotion'][0]['value'] ?? false;

            if ($isHidden) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Remove the current node from the right side block
     */
    private function removeNodeFromCollection($node, &$collection)
    {
        $nid = $node['nid'][0]['value'];

        $collection = array_filter($collection, function ($value, $key) use ($nid) {
            if ($value['nid'][0]['value'] == $nid) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
