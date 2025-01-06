<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class DropdownMenuAsync implements AsyncSectionInterface
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
            'base' => $this->config->getGeneralConfigById('dropdown_menu_settings'),
        ];
    }

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function processDefinition($data, array $options)
    {
        return $data['base'];
    }
}
