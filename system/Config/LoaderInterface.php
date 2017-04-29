<?php

namespace Mini\Config;


interface LoaderInterface
{

    /**
     * Load the given configuration group.
     *
     * @param  string  $environment
     * @param  string  $group
     * @return array
     */
    public function load($group);

    /**
     * Determine if the given configuration group exists.
     *
     * @param  string  $group
     * @return bool
     */
    public function exists($group);

}
