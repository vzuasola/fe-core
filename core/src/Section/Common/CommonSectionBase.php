<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

abstract class CommonSectionBase
{
    private static $data;
    private static $defined;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->fetcher = $container->get('common_fetcher');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        $definition = [];

        if (!isset(self::$defined)) {
            $definition = [
                'base' => $this->fetcher->getData(),
            ];

            self::$defined = true;
        }

        return $definition;
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        if (!isset(self::$data)) {
            self::$data = $data;
        }

        return self::$data;
    }
}
