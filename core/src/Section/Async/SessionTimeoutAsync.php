<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;
use App\Form\LoginForm;
use App\Form\LoginLightboxForm;
use App\Utils\Url;
use DateTimeZone;
use DateTime;
use App\Drupal\Config;

class SessionTimeoutAsync implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->config = $container->get('config_fetcher_async');
        $this->scripts = $container->get('scripts');
        $this->settings = $container->get('settings');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'base' => $this->config->getGeneralConfigById('login_configuration'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
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
