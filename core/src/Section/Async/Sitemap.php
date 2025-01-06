<?php

namespace App\Section\Async;

use App\Drupal\Config;
use App\Plugins\Section\AsyncSectionInterface;

use Interop\Container\ContainerInterface;

/**
 * Sitemap section for Dafa products
 */
class Sitemap extends SitemapBase implements AsyncSectionInterface
{
    /**
     * The config fetcher
     *
     * @var object
     */
    private $config;

    /**
     * The menu fetcher
     *
     * @var object
     */
    private $menu;

    /**
     * The token parser
     *
     * @var object
     */
    private $token;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);

        $this->config = $container->get('config_fetcher_async');
        $this->menu = $container->get('menu_fetcher_async');
        $this->token = $container->get('token_parser');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        $definitions = parent::getSectionDefinition($options);

        return $definitions + [
            'footer_configuration' => $this->config->getGeneralConfigById('footer_configuration'),
            'quicklinks' => $this->menu->getMultilingualMenu('quicklinks'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = parent::processDefinition($data, $options);

        $title = $data['footer_configuration']['quicklinks_title'];
        $links = $data['quicklinks'];

        $result['links']['quicklinks'] = $this->getQuickLinks($title, $links);

        return $result;
    }

    /**
     *
     */
    private function getQuickLinks($title, $links)
    {
        $result = [
            'label' => $title,
        ];

        foreach ($links as $value) {
            $path = $value['alias'] ? $value['alias'] : $value['uri'];
            $path = $this->token->processTokens($path);
            $result['path'][] = [
                'label' => $value['title'],
                'path' => $path,
                'frequency' => 'daily',
                'priority' => '0.5',
            ];
        }

        return $result;
    }
}
