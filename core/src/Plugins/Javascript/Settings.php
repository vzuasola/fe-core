<?php

namespace App\Plugins\Javascript;

/**
 *
 */
class Settings
{
    /**
     * Store file includes
     *
     * @var array
     */
    protected $includes = [];

    /**
     * Store script includes
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * The script provider manager
     *
     * @var App\Plugins\Javascript\ScriptManager
     */
    private $manager;

    /**
     * Public constructor
     *
     * @param App\Plugins\Javascript\ScriptManager $manager The script manager
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Attach data to Javascript
     *
     * @param array $script An associative array of data
     */
    public function attach(array $script, $options = [])
    {
        if (isset($options['skip']) && $options['skip']) {
            return;
        }

        if ($options === true) {
            $concat = array_replace_recursive($this->scripts, $script);
        } else {
            $concat = array_replace($this->scripts, $script);
        }

        $this->scripts = $concat;
    }

    /**
     * Include an external Javascript file
     *
     * @param string $file
     */
    public function add($file)
    {
        $this->includes[] = $file;
    }

    /**
     * Gets the JSON encoded scripts
     *
     * @return string
     */
    public function getScripts()
    {
        if ($this->scripts) {
            return $this->scripts;
        }
    }

    /**
     * Gets the JSON encoded scripts
     *
     * @return string
     */
    public function getIncludes()
    {
        if ($this->includes) {
            return $this->includes;
        }
    }
}
