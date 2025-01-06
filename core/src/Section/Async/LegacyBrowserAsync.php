<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

/**
 *
 */
class LegacyBrowserAsync implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->config = $container->get('config_fetcher_async');
        $this->token = $container->get('token_parser');
        $this->scripts = $container->get('scripts');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'base' => $this->config->getGeneralConfigById('browser_configuration'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        if (isset($data['base'])) {
            $result = $data['base'];

            if (!empty($result['message']['value'])) {
                $result['message']['value'] = $this->token->processTokens($result['message']['value']);
            }

            $this->scripts->attach([
                'outdated_browser' => $result,
            ], $options);
        }

        return $result;
    }
}
