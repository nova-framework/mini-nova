<?php

namespace Mini\View;

use Mini\Support\Contracts\ArrayableInterface as Arrayable;
use Mini\View\View;

use BadMethodCallException;


class Factory
{
    /**
     * @var array Array of shared data
     */
    protected $shared = array();


    /**
     * Create a View instance
     *
     * @param string $path
     * @param array|string $data
     * @param string|null $module
     * @return \Nova\View\View
     * @throws \BadMethodCallException
     */
    public function make($view, $data = array())
    {
        $path = $this->getViewPath($view);

        if (! is_readable($path)) {
            throw new BadMethodCallException("File path [$path] does not exist");
        }

        return new View($this, $view, $path, $this->parseData($data));
    }

    /**
     * Get the rendered string contents of a View.
     *
     * @param mixed $view
     * @param array $data
     *
     * @return string
     */
    public function fetch($view, $data = array(), Closure $callback = null)
    {
        return $this->make($view, $data)->render($callback);
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param  mixed  $data
     * @return array
     */
    protected function parseData($data)
    {
        return ($data instanceof Arrayable) ? $data->toArray() : $data;
    }

    /**
     * Add a piece of shared data to the Factory.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function share($key, $value = null)
    {
        if ( ! is_array($key)) return $this->shared[$key] = $value;

        foreach ($key as $innerKey => $innerValue) {
            $this->share($innerKey, $innerValue);
        }
    }

    /**
     * Get an item from the shared data.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function shared($key, $default = null)
    {
        return array_get($this->shared, $key, $default);
    }

    /**
     * Get all of the shared data for the Factory.
     *
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Check if the view file exists.
     *
     * @param    string     $view
     * @return    bool
     */
    public function exists($view)
    {
        $path = $this->getViewPath($view);

        return file_exists($path);
    }

    /**
     * Get the view file.
     *
     * @param    string     $view
     * @return    string
     */
    protected function getViewPath($view)
    {
        return APPPATH .str_replace('/', DS, "Views/$view.php");
    }
}
