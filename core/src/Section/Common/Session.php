<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class Session extends CommonSectionBase implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);

        $this->scripts = $container->get('scripts');
        $this->settings = $container->get('settings');
    }

    /**
     *
     */
    public function processDefinition($data, array $options)
    {
        $result = [];
        $data = parent::processDefinition($data, $options);

        if (isset($data['base']['session'])) {
            $result = $this->getSectionData($data['base']['session'], $options);
        }

        return $result;
    }

    /**
     *
     */
    protected function getSectionData($data, array $options)
    {
        $result = [];

        if (isset($data['base'])) {
            $timeout = $data['base']['session_maxtime'] * 60;

            $this->scripts->attach([
                'sessionTimeout' => $timeout,
            ], $options);

            $result = $data['base'];
        }

        return $result;
    }
}
