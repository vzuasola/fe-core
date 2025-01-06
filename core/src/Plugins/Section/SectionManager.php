<?php

namespace App\Plugins\Section;

use App\Async\Definition;
use App\Async\DefinitionCollection;

/**
 * Section Manager
 */
class SectionManager
{
    /**
     * Exposed the service container on the form manager
     */
    protected $container;

    /**
     * The system configurations manager
     */
    protected $configuration;

    /**
     * Stores the defined sections
     *
     * @var array
     */
    private $sectionList = [];

    /**
     * Stores the defined section alters
     *
     * @var array
     */
    private $alterList = [];

    /**
     * Public constructor
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->configuration = $container->get('configuration_manager');
    }

    /**
     * Gets all list of defined sections
     */
    public function getSectionList()
    {
        if (empty($this->sectionList)) {
            $values = $this->configuration->getConfiguration('sections');

            if (!empty($values['sections'])) {
                $this->sectionList = $values['sections'];
            }
        }

        return $this->sectionList;
    }

    /**
     * Gets all list of defined section alters
     */
    public function getAlterList()
    {
        if (empty($this->alterList)) {
            $values = $this->configuration->getConfiguration('sections');

            if (!empty($values['alters'])) {
                $this->alterList = $values['alters'];
            }
        }

        return $this->alterList;
    }

    /**
     *
     */
    public function getSection($id, $options = [])
    {
        $list = $this->getSectionList();
        $section = new $list[$id];

        // inject the service container
        if (method_exists($section, 'setContainer')) {
            $section->setContainer($this->container);
        }

        $list = $this->getAlterList();

        if (isset($list[$id])) {
            $alter = new $list[$id];

            // inject the service container
            if (method_exists($alter, 'setContainer')) {
                $alter->setContainer($this->container);
            }
        } else {
            $alter = null;
        }

        if ($section instanceof AsyncSectionInterface) {
            $data = $this->getAsyncSectionDefinition($section, $options, $alter);
        } else {
            $data = $this->getSectionDefinition($section, $options, $alter);
        }

        return $data;
    }

    /**
     * Get Single Definitions
     *
     */

    /**
     *
     */
    private function getAsyncSectionDefinition(
        AsyncSectionInterface $section,
        $options,
        AsyncSectionAlterInterface $alter = null
    ) {
        $definitions = $section->getSectionDefinition($options);

        if (isset($alter)) {
            $alter->alterSectionDefinition($definitions, $options);
        }

        $callback = function ($data, $options, $response) use ($section, $alter) {
            $result = $section->processDefinition($data, $options, $response);

            if (isset($alter)) {
                $alter->alterprocessDefinition($result, $data, $options, $response);
            }

            return $result;
        };

        return new DefinitionCollection($definitions, $options, $callback);
    }

    /**
     *
     */
    private function getSectionDefinition(
        SectionInterface $section,
        $options,
        SectionAlterInterface $alter = null
    ) {
        $result =  $section->getSection($options);

        if (isset($alter)) {
            $alter->alterSection($result, $options);
        }

        return $result;
    }
}
