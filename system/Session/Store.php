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

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;


class Store implements SessionInterface, \ArrayAccess
{
    /**
     * The session ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The session name.
     *
     * @var string
     */
    protected $name;

    /**
     * The session attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The session bags.
     *
     * @var array
     */
    protected $bags = array();

    /**
     * The meta-data bag instance.
     *
     * @var \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    protected $metaBag;

    /**
     * Local copies of the session bag data.
     *
     * @var array
     */
    protected $bagData = array();

    /**
     * Session store started status.
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Session store closed status.
     *
     * @var bool
     */
    protected $closed = false;


    /**
     * Create a new Session Store instance.
     *
     * @param  string  $name
     * @return void
     */
    public function __construct($name)
    {
        $this->setName($name);

        $this->metaBag = new MetadataBag();
    }

    /**
     * Load the session with attributes.
     *
     * @param array|null $session
     */
    protected function loadSession(array &$session = null)
    {
        if (is_null($session)) {
            $session = &$_SESSION;
        }

        $bags = array_merge($this->bags, array($this->metaBag));

        foreach ($bags as $bag) {
            $key = $bag->getStorageKey();

            $value = Arr::get($session, $key, array());

            //
            $session[$key] = $value;

            $bag->initialize($value);
        }

        $this->started = true;
        $this->closed = false;
    }

    /**
     * Start the Session.
     *
     * @return \Session\Store
     * @throws \RuntimeException
     */
    public function start()
    {
        if ($this->started) {
            return true;
        }

        if (\PHP_SESSION_ACTIVE === session_status()) {
            throw new \RuntimeException('Failed to start the session: already started by PHP.');
        }

        session_start();

        $this->loadSession();

        if (! $this->has('_token')) {
            $this->regenerateToken();
        }

        return true;
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
     * {@inheritdoc}
     */
    public function setId($id)
    {
        if (! $this->isValidId($id)) {
            $id = $this->generateSessionId();
        }

        $this->id = $id;

        return session_id($id);
    }

    /**
     * Determine if this is a valid session ID.
     *
     * @param  string  $id
     * @return bool
     */
    public function isValidId($id)
    {
        return (is_string($id) && preg_match('/^[a-f0-9]{40}$/', $id));
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return sha1(uniqid('', true) .str_random(25) .microtime(true));
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
        $this->name = $name;

        return session_name($name);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($lifeTime = null)
    {
        $this->clear();

        return $this->migrate(true, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function migrate($destroy = false, $lifeTime = null)
    {
        return $this->regenerate($destroy, $lifeTime);
    }

    /**
     * Generate a new session identifier.
     *
     * @param  bool  $destroy
     * @return bool
     */
    public function regenerate($destroy = false, $lifeTime = null)
    {
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            return false;
        }

        if (! is_nulol($lifeTime)) {
            ini_set('session.cookie_lifetime', $lifeTime);
        }

        if ($destroy) {
            $this->metaBag->stampNew();
        }

        $isRegenerated = session_regenerate_id($destroy);

        // The reference to $_SESSION in session bags is lost in PHP7 and we need to re-create it.
        // @see https://bugs.php.net/bug.php?id=70013
        $this->loadSession();

        return $isRegenerated;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $bags = array_merge($this->bags, array($this->metaBag));

        foreach ($bags as $bag)  {
            $key = $bag->getStorageKey();

            $this->put($key, $this->bagData[$key]);
        }

        //
        session_write_close();

        $this->started = false;
        $this->closed  = true;
    }

    /**
     * Determine if an item exists in the Session.
     *
     * @param  string  $name
     * @return mixed
     */
    public function has($name)
    {
        return ! is_null($this->get($name));
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
     * Get the value of a given key and then forget it.
     *
     * @param  string  $key
     * @param  string  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->attributes, $key, $default);
    }

    /**
     * Determine if the session contains old input.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasOldInput($key = null)
    {
        $old = $this->getOldInput($key);

        return is_null($key) ? (count($old) > 0) : ! is_null($old);
    }

    /**
     * Get the requested item from the flashed input array.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function getOldInput($key = null, $default = null)
    {
        $input = $this->get('_old_input', array());

        return Arr::get($input, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        Arr::set($_SESSION, $name, $value);
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
     * {@inheritdoc}
     */
    public function replace(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->put($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        return Arr::pull($_SESSION, $name);
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
     * {@inheritdoc}
     */
    public function clear()
    {
        foreach ($this->bags as $bag) {
            $bag->clear();
        }

        $_SESSION = array();

        $this->loadSession();
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBag(SessionBagInterface $bag)
    {
        $key = $bag->getStorageKey();

        $this->bags[$key] = $bag;
    }

    /**
     * {@inheritdoc}
     */
    public function getBag($name)
    {
        return Arr::get($this->bags, $name, function()
        {
            throw new \InvalidArgumentException("Bag not registered.");
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataBag()
    {
        return $this->metaBag;
    }

    /**
     * Get the raw bag data array for a given bag.
     *
     * @param  string  $name
     * @return array
     */
    public function getBagData($name)
    {
        return array_get($this->bagData, $name, array());
    }

    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function flush()
    {
        $this->clear();
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
     * Flash an input array to the session.
     *
     * @param  array  $value
     * @return void
     */
    public function flashInput(array $value)
    {
        $this->flash('_old_input', $value);
    }

    /**
     * Delete all the flashed data.
     *
     * @return void
     */
    public function clearFlash()
    {
        foreach ($this->get('flash', array()) as $key) {
            $this->forget($key);
        }

        $this->put('flash', array());
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
        $this->put('_token', Str::random(40));
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
