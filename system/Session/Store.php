<?php
/**
 * Store - A Class which implements a Session Store.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Mini\Session;

use Mini\Support\Arr;
use Mini\Support\Str;


class Store implements \ArrayAccess
{
    /**
     * Create a new Session Store instance.
     *
     * @param  string  $name
     * @return void
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Start the Session.
     *
     * @return \Session\Store
     */
    public function start()
    {
        if (! $this->getId()) {
            session_start();
        }

        if (! $this->has('_token')) {
            $this->regenerateToken();
        }

        return $this;
    }

    /**
     * Get the current Session id.
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Set the current Session ID.
     *
     * @param  string  $id
     * @return void
     */
    public function setId($id)
    {
        return session_id($id);
    }

    /**
     * Get the current Session name.
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Set the current Session name.
     *
     * @param  string  $name
     * @return string
     */
    public function setName($name)
    {
        return session_name($name);
    }

    /**
     * Determine if an item exists in the Session.
     *
     * @param  string  $name
     * @return mixed
     */
    public function has($name)
    {
        return ! is_nul($this->get($name));
    }

    /**
     * Retrieve an item from the Session.
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return Arr::get($_SESSION, $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        Arr::set($_SESSION, $name, $value);
    }

    /**
     * Set a key / value pair or array of key / value pairs in the Session.
     *
     * @param  string|array  $key
     * @param  mixed|null    $value
     * @return void
     */
    public function put($key, $value = null)
    {
        if (! is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * Push a value onto an array Session value.
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, array());

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * Flash a key / value pair to the Session.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function flash($key, $value)
    {
        $this->put($key, $value);

        $this->push('flash', $key);
    }


    /**
     * Delete all the flashed data.
     *
     * @return void
     */
    public function deleteFlash()
    {
        foreach ($this->get('flash', array()) as $key) {
            $this->delete($key);
        }

        $this->put('flash', array());
    }

    /**
     * Retrieve all items from the Session.
     *
     * @return array
     */
    public function all()
    {
        return $_SESSION;
    }

    /**
     * Remove an item from the Session.
     *
     * @param  string  $key
     * @return void
     */
    public function forget($key)
    {
        Arr::forget($_SESSION, $key);
    }

    /**
     * Destroy all data registered to a Session.
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->getId()) {
            return session_destroy();
        }
    }

    /**
     * Remove all items from the Session.
     *
     * @return bool
     */
    public function flush()
    {
        return session_unset();
    }

    /**
     * Get CSRF token value.
     *
     * @return void
     */
    public function token()
    {
        return $this->get('_token');
    }

    /**
     * Regenerate the CSRF token value.
     *
     * @return void
     */
    public function regenerateToken()
    {
        $this->put('_token', Str::random(128));
    }

    /**
     * Get the previous URL from the session.
     *
     * @return string|null
     */
    public function previousUrl()
    {
        return $this->get('_previous.url');
    }

    /**
     * Set the "previous" URL in the session.
     *
     * @param  string  $url
     * @return void
     */
    public function setPreviousUrl($url)
    {
        return $this->put('_previous.url', $url);
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
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->put($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    }
}
