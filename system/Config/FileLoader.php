<?php

namespace Mini\Config;

use Mini\Config\LoaderInterface;


class FileLoader implements LoaderInterface
{
    /**
     * The default configuration path.
     *
     * @var string
     */
    protected $defaultPath;

    /**
     * A cache of whether namespaces and groups exists.
     *
     * @var array
     */
    protected $exists = array();


    /**
     * Create a new file configuration loader.
     *
     * @param  string  $defaultPath
     * @return void
     */
    public function __construct($defaultPath)
    {
        $this->defaultPath = $defaultPath;
    }

    /**
     * Load the given configuration group.
     *
     * @param  string  $environment
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    public function load($group)
    {
        $items = array();

        //
        $file = $this->getPath() .ucfirst($group) .'.php';

        if (is_readable($file)) {
            $items = (array) $this->getRequire($file);
        }

        return $items;
    }

    /**
     * Determine if the given group exists.
     *
     * @param  string  $group
     * @param  string  $namespace
     * @return bool
     */
    public function exists($group)
    {
        if (isset($this->exists[$group])) {
            return $this->exists[$group];
        }

        $file = $this->getPath() .ucfirst($group) .'.php';

        return $this->exists[$group] = is_readable($file);
    }

    /**
     * Get the configuration path for a namespace.
     *
     * @param  string  $namespace
     * @return string
     */
    protected function getPath()
    {
        return $this->defaultPath .DS;
    }

    /**
     * Get a file's contents by requiring it.
     *
     * @param  string  $path
     * @return mixed
     */
    protected function getRequire($path)
    {
        return require $path;
    }

}
