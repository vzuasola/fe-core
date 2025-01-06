<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class AnnouncementAsync implements AsyncSectionInterface
{
    /**
     * The service container
     */
    private $container;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->config = $container->get('config_fetcher_async');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'base' => $this->config->getGeneralConfigById('header_configuration'),
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

        $headerConfig = $data['base'];

        $announcement = $headerConfig['critical_announcement'] ?? null;
        $content = $headerConfig['critical_announcement_content']['value'] ?? null;

        if ($announcement && $content) {
            $result['critical_announcement'] = $announcement;
            $result['critical_announcement_content'] = $content;
        }

        $announcement = $headerConfig['news_announcement'] ?? null;
        $content = $headerConfig['news_announcement_content']['value'] ?? null;

        if ($announcement && $content) {
            $result['news_announcement'] = $announcement;
            $result['news_announcement_content'] = $content;
        }

        return $result;
    }
}
