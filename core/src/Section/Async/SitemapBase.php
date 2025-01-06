<?php

namespace App\Section\Async;

use App\Drupal\Config;
use App\Plugins\Section\AsyncSectionInterface;

use Interop\Container\ContainerInterface;

/**
 * The sitemap will return an array of 2 sub keys, one for configuration, and one
 * for links. The link data will return a value of a tree
 *
 * $link = [
 *     'label' => 'Node One',
 *     'path' => '/node/one',
 *     'frequency' => 'daily',
 *     'priority' => '0.7',
 * ];
 *
 * And it will return a nested tree having this format
 *
 * $link = [
 *     'label' => 'Node One',
 *     'path' => [
 *         [
 *             'label' => 'Nested Node One',
 *             'path' => '/node/nested/one',
 *             'frequency' => 'daily',
 *             'priority' => '0.7',
 *         ],
 *         [
 *             'label' => 'Nested Node Two',
 *             'path' => '/node/nested/two'
 *             'frequency' => 'daily',
 *             'priority' => '0.7',
 *         ],
 *     ]
 * ];
 *
 * Available options
 *     callable 'filter_node' A callback that will be passed to array_filter to filter the nodes
 *     callable 'on_disable' A callback that will be invoked when the sitemap is disabled
 */
class SitemapBase implements AsyncSectionInterface
{
    const VIEW_ID = 'sitemap';
    const CONFIG_ID = 'sitemap_configuration';

    /**
     * The views object
     *
     * @var object
     */
    private $views;

    /**
     * The config fetcher
     *
     * @var object
     */
    private $config;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->views = $container->get('views_fetcher_async');
        $this->config = $container->get('config_fetcher_async');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'sitemap_configuration' => $this->config->getGeneralConfigById(self::CONFIG_ID),
            'nodes' => $this->views->getViewById(self::VIEW_ID),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        $nodes = $data['nodes'];
        $settings = $data['sitemap_configuration'];

        $staticLinks = $this->getStaticLinks($settings, $options);
        $nodeLinks = $this->getNodesLinks($settings, $nodes, $options);

        $result['config'] = $settings;
        $result['links'] = $staticLinks + $nodeLinks;

        return $result;
    }

    /**
     * Get the sitemap links from static links
     *
     * @return array
     */
    private function getStaticLinks($settings, $options)
    {
        $result = [];

        // invoke the custom hook for disabled sitemap
        if (empty($settings['enable_sitemap']) &&
            isset($options['on_disable']) &&
            is_callable($options['on_disable'])
        ) {
            $callback = $options['on_disable'];
            $callback();
        }

        if (isset($settings['sitemap_links'])) {
            $parsed = Config::parse($settings['sitemap_links']);

            foreach ($parsed as $key => $value) {
                $result[] = [
                    'label' => $key,
                    'path' => $value,
                    'frequency' => 'daily',
                    'priority' => '0.7',
                ];
            }
        }

        return $result;
    }

    /**
     * Get the sitemap links from nodes
     *
     * @return array
     */
    private function getNodesLinks($settings, $nodes, $options)
    {
        $result = [];

        if (isset($settings['content_types'])) {
            $list = [];

            foreach ($settings['content_types'] as $key => $value) {
                if (!empty($value['enable'])) {
                    $list[$key] = $value['label'] ?? ucwords($key);
                }
            }

            // invoke the custom filtering function for nodes
            if (isset($options['filter_node']) && is_callable($options['filter_node'])) {
                $nodes = array_filter($nodes, $options['filter_node']);
            }

            $result = $this->getNodesFromList($nodes, $list);

            uasort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        }

        return $result;
    }

    /**
     *
     */
    private function getNodesFromList($nodes, $list)
    {
        $result = [];

        foreach ($nodes as $node) {
            $id = $node['type'][0]['target_id'];

            if (isset($list[$id])) {
                if (!isset($result[$id]['path'])) {
                    $result[$id]['label'] = $list[$id];
                }

                $entry = $this->getNodeSitemapLinks($node);
                if (!empty($entry)) {
                    $result[$id]['path'][] = $entry;
                }
            }
        }

        return $result;
    }

    /**
     *
     */
    private function getNodeSitemapLinks($node)
    {
        $result = [];

        if (isset($node['title'][0]['value']) &&
            isset($node['alias'][0]['value'])
        ) {
            $result = [
                'label' => $node['title'][0]['value'],
                'path' => $node['alias'][0]['value'],
                'frequency' => 'daily',
                'priority' => '0.5',
            ];
        }

        return $result;
    }
}
