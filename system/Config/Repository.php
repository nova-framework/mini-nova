<?php

namespace Mini\Config;

use Mini\Support\Arr;

use Closure;
use ArrayAccess;


class Repository implements ArrayAccess
{
    /**
     * The loader implementation.
     *
     * @var \Mini\Config\LoaderInterface
     */
    protected $loader;

    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = array();


    /**
     * Create a new configuration repository.
     *
     * @param  \Mini\Config\LoaderInterface  $loader
     * @return void
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        $default = microtime(true);

        return $this->get($key, $default) !== $default;
    }

    /**
     * Determine if a configuration group exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGroup($key)
    {
        list($group, $item) = $this->parseKey($key);

        return $this->loader->exists($group, $namespace);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        list($group, $item) = $this->parseKey($key);

        $this->load($group);

        return Arr::get($this->items[$group], $item, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function set($key, $value)
    {
        list($group, $item) = $this->parseKey($key);

        //
        $this->load($group);

        if (is_null($item)) {
            $this->items[$group] = $value;
        } else {
            Arr::set($this->items[$group], $item, $value);
        }
    }

    /**
     * Load the configuration group for the key.
     *
     * @param  string  $group
     * @param  string  $namespace
     * @param  string  $collection
     * @return void
     */
    protected function load($group)
    {
        if (! isset($this->items[$group])) {
            $this->items[$group] = $this->loader->load($group);
        }
    }

    /**
     * Parse a key into group, and item.
     *
     * @param  string  $key
     * @return array
     */
    public function parseKey($key)
    {
        return array_pad(explode('.', $key, 2), 2, null);
    }

    /**
     * Get the loader implementation.
     *
     * @return \Mini\Config\LoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Set the loader implementation.
     *
     * @param  \Mini\Config\LoaderInterface  $loader
     * @return void
     */
    public function setLoader(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Get all of the configuration items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }

}
