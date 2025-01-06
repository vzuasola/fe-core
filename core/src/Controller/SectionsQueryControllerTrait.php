<?php

namespace App\Controller;

use App\Async\Async;

/**
 *
 */
trait SectionsQueryControllerTrait
{
    /**
     * @{inheritdoc}
     */
    public function sectionsQuery()
    {
        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function withArguments($args)
    {
        $this->sectionArgs = $args;

        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function addDefinitions($definitions)
    {
        if (isset($this->sectionDefinitions)) {
            $this->sectionDefinitions = array_replace($this->sectionDefinitions, $definitions);
        } else {
            $this->sectionDefinitions = $definitions;
        }

        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function addCollection($name)
    {
        $name = str_replace('_', '', ucwords($name, '_'));
        $method = lcfirst("{$name}Collection");

        $sections = $this->{$method}();

        if (isset($this->sectionDefinitions)) {
            $this->sectionDefinitions = array_replace($this->sectionDefinitions, $sections);
        } else {
            $this->sectionDefinitions = $sections;
        }

        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function addPreprocess($process)
    {
        if (!isset($this->preprocess)) {
            $this->preprocess = [];
        }

        array_push($this->preprocess, $process);

        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function getSections()
    {
        $data = false;

        if (isset($this->sectionDefinitions)) {
            $data = Async::resolve($this->sectionDefinitions);
        }

        if (isset($this->preprocess)) {
            foreach ($this->preprocess as $process) {
                $process = ucfirst($process);
                $method = "preprocess$process";

                $this->{$method}($data);
            }
        }

        return $data;
    }
}
