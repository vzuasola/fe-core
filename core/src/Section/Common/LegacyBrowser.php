<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class LegacyBrowser extends CommonSectionBase implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->scripts = $container->get('scripts');
        $this->token = $container->get('token_parser');
    }

    /**
     *
     */
    public function processDefinition($data, array $options)
    {
        $result = [];
        $data = parent::processDefinition($data, $options);

        if (isset($data['base']['outdated_browser']['base'])) {
            $result = $data['base']['outdated_browser']['base'] ?? 'Error retrieving';

            if (!empty($result['message']['value'])) {
                $result['message']['value'] = $this->token->processTokens($result['message']['value']);
            }

            // attach scripts to page
            $this->scripts->attach([
                'outdated_browser' => $result,
            ], $options);
        }

        return $result;
    }
}
