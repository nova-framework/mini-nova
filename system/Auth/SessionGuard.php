<?php
/**
 * Guard - A simple Authentication Guard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace Mini\Auth;

use Mini\Auth\Contracts\GuardInterface;
use Mini\Auth\Contracts\UserInterface;
use Mini\Auth\GuardTrait;
use Mini\Cookie\CookieJar;
use Mini\Encryption\DecryptException;
use Mini\Encryption\Encrypter;
use Mini\Encryption\EncryptException;
use Mini\Events\Dispatcher;
use Mini\Hashing\HasherInterface;
use Mini\Http\Request;
use Mini\Session\Store as SessionStore;
use Mini\Support\Str;


class SessionGuard implements GuardInterface
{
	use GuardTrait;

	/**
	 * The name of the Guard.
	 *
	 * Corresponds to driver name in authentication configuration.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The user we last attempted to retrieve.
	 *
	 * @var \Mini\Auth\Contracts\UserInterface
	 */
	protected $lastAttempted;

	/**
	 * Indicates if the User was authenticated via a recaller Cookie.
	 *
	 * @var bool
	 */
	protected $viaRemember = false;

	/**
	 * The ORM user model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * The session store used by the guard.
	 *
	 * @var \Mini\Session\Store
	 */
	protected $session;

	/**
	 * The Nova cookie creator service.
	 *
	 * @var \Mini\Cookie\CookieJar
	 */
	protected $cookie;

	/**
	 * The request instance.
	 *
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * The hasher implementation.
	 *
	 * @var \Mini\Hashing\HasherInterface
	 */
	protected $hasher;

	/**
	 * The Nova encrypter service.
	 *
	 * @var \Mini\Encryption\Encrypter
	 */
	protected $encrypter;

	/**
	 * The event dispatcher instance.
	 *
	 * @var \Mini\Events\Dispatcher
	 */
	protected $events;

	/**
	 * Indicates if the logout method has been called.
	 *
	 * @var bool
	 */
	protected $loggedOut = false;

	/**
	 * Indicates if a token user retrieval has been attempted.
	 *
	 * @var bool
	 */
	protected $tokenRetrievalAttempted = false;


	/**
	 * Create a new authentication guard.
	 *
	 * @param  string  $name
	 * @param  string  $model
	 * @param  \Mini\Session\Store  $session
	 * @param  \Mini\Hashing\HasherInterface  $hasher
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @return void
	 */
	public function __construct($name,
								$model,
								SessionStore $session,
								HasherInterface $hasher,
								Encrypter $encrypter,
								CookieJar $cookie,
								Dispatcher $events,
								Request $request = null)
	{
		$this->name  = $name;
		$this->model = $model;

		$this->session		= $session;
		$this->hasher		= $hasher;
		$this->encrypter	= $encrypter;
		$this->cookie		= $cookie;
		$this->events		= $events;

		$this->request = $request;
	}

	/**
	 * Get the authenticated user.
	 *
	 * @return \Mini\Auth\Contracts\UserInterface|null
	 */
	public function user()
	{
		if ($this->loggedOut || ! is_null($this->user)) {
			return $this->user;
		}

		$user = null;

		$id = $this->session->get($this->getName());

		if (! is_null($id)) {
			$user = $this->retrieveUserById($id);
		}

		if (is_null($user) && ! is_null($recaller = $this->getRecaller())) {
			$user = $this->getUserByRecaller($recaller);

			if (! is_null($user)) {
				$this->updateSession($user->getAuthIdentifier());
			}
		}

		return $this->user = $user;
	}

	/**
	 * Get the ID for the currently authenticated User.
	 *
	 * @return int|null
	 */
	public function id()
	{
		if ($this->loggedOut) {
			return null;
		}

		$id = $this->session->get($this->getName(), $this->getRecallerId());

		if (is_null($id) && ! is_null($user = $this->user())) {
			$id = $user->getAuthIdentifier();
		}

		return $id;
	}

	/**
	 * Validate a user's credentials.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validate(array $credentials = array())
	{
		return $this->attempt($credentials, false, false);
	}

	/**
	 * Get a user by its recaller ID.
	 *
	 * @param  string $recaller
	 * @return mixed
	 */
	protected function getUserByRecaller($recaller)
	{
		if ($this->validRecaller($recaller) && ! $this->tokenRetrievalAttempted) {
			$this->tokenRetrievalAttempted = true;

			list($id, $token) = explode('|', $recaller, 2);

			$this->viaRemember = ! is_null($user = $this->retrieveUserByToken($id, $token));

			return $user;
		}
	}

	/**
	 * Get the decrypted Recaller cookie.
	 *
	 * @return string|null
	 */
	protected function getRecaller()
	{
		$cookie = $this->getRecallerName();

		$value = $this->request->cookies->get($cookie);

		if (! is_null($value)) {
			return $this->decryptCookie($value);
		}
	}

	/**
	 * Decrypt a cookie string.
	 *
	 * @param string $cookie
	 * @return string|null
	 */
	protected function decryptCookie($cookie)
	{
		try {
			return $this->getEncrypter()->decrypt($cookie);
		} catch (EncryptException $e) {
			//
		}
	}

	/**
	 * Get the user ID from the recaller Cookie.
	 *
	 * @return string
	 */
	protected function getRecallerId()
	{
		if ($this->validRecaller($recaller = $this->getRecaller())) {
			return reset(explode('|', $recaller));
		}
	}

	/**
	 * Determine if the recaller Cookie is in a valid format.
	 *
	 * @param  string $recaller
	 * @return bool
	 */
	protected function validRecaller($recaller)
	{
		if (is_string($recaller) && (strpos($recaller, '|') !== false)) {
			$segments = explode('|', $recaller);

			return (count($segments) == 2) && (trim($segments[0]) !== '') && (trim($segments[1]) !== '');
		}

		return false;
	}

	/**
	 * Log a user into the application without sessions or cookies.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	public function once(array $credentials = array())
	{
		if ($this->validate($credentials)) {
			$this->setUser($this->lastAttempted);

			return true;
		}

		return false;
	}

	/**
	 * Attempt to authenticate a User, using the given credentials.
	 *
	 * @param  array $credentials
	 * @param  bool  $remember
	 * @param  bool  $login
	 * @return bool
	 */
	public function attempt(array $credentials = array(), $remember = false, $login = true)
	{
		$this->fireAttemptEvent($credentials, $remember, $login);

		$this->lastAttempted = $user = $this->retrieveUser($credentials);

		if (! $this->hasValidCredentials($user, $credentials)) {
			return false;
		}

		if ($login) {
			$this->login($user, $remember);
		}

		return true;
	}

	/**
	 * Fire the attempt event with the arguments.
	 *
	 * @param  array  $credentials
	 * @param  bool   $remember
	 * @param  bool   $login
	 * @return void
	 */
	protected function fireAttemptEvent(array $credentials, $remember, $login)
	{
		$payload = array($credentials, $remember, $login);

		$this->events->fire('auth.attempt', $payload);
	}

	/**
	 * Register an authentication attempt event listener.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function attempting($callback)
	{
		$this->events->listen('auth.attempt', $callback);
	}

	/**
	 * Determine if the user matches the credentials.
	 *
	 * @param  mixed  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	protected function hasValidCredentials($user, $credentials)
	{
		if (is_null($user)) {
			return false;
		}

		$plain = $credentials['password'];

		return $this->hasher->check($plain, $user->getAuthPassword());
	}

	/**
	 * Log a User in.
	 *
	 * @param  \Mini\Auth\Contracts\UserInterface $user
	 * @param  bool $remember
	 * @return void
	 */
	public function login(UserInterface $user, $remember = false)
	{
		$this->updateSession($user->getAuthIdentifier());

		if ($remember) {
			// Create a new remember token for the user if one doesn't already exist.
			$rememberToken = $user->getRememberToken();

			if (empty($rememberToken)) {
				$this->refreshRememberToken($user);
			}

			$this->queueRecallerCookie($user);
		}

		$this->events->fire('auth.login', array($user, $remember));

		$this->setUser($user);
	}

	/**
	 * Update the Session with the given ID.
	 *
	 * @param  string $id
	 * @return void
	 */
	protected function updateSession($id)
	{
		$this->session->put($this->getName(), $id);

		$this->session->migrate(true);
	}

	/**
	 * Log the given user ID into the application.
	 *
	 * @param  mixed  $id
	 * @param  bool   $remember
	 * @return \Mini\Auth\Contracts\UserInterface
	 */
	public function loginUsingId($id, $remember = false)
	{
		$this->session->put($this->getName(), $id);

		$user = $this->retrieveUserById($id);

		$this->login($user, $remember);

		return $user;
	}

	/**
	 * Log the given user ID into the application without sessions or cookies.
	 *
	 * @param  mixed  $id
	 * @return bool
	 */
	public function onceUsingId($id)
	{
		$user = $this->retrieveUserById($id);

		$this->setUser($user);

		return ($user instanceof UserInterface);
	}

	/**
	 * Set the recaller Cookie.
	 *
	 * @param  \Mini\Auth\Contracts\UserInterface $user
	 * @return void
	 */
	protected function queueRecallerCookie(UserInterface $user)
	{
		$value = $user->getAuthIdentifier() .'|' .$user->getRememberToken();

		if (! is_null($cookie = $this->encryptCookie($value))) {
			$cookies = $this->getCookieJar();

			$cookies->queue($cookies->forever($this->getRecallerName(), $cookie));
		}
	}

	/**
	 * Encrypt a cookie string.
	 *
	 * @param string $cookie
	 * @return string|null
	 */
	protected function encryptCookie($cookie)
	{
		try {
			return $this->getEncrypter()->encrypt($cookie);
		} catch (EncryptException $e) {
			//
		}
	}

	/**
	 * Log the user out.
	 *
	 * @return void
	 */
	public function logout()
	{
		$user = $this->user();

		// Remove the user data from the session and cookies.
		$this->session->forget($this->getName());

		$cookies = $this->getCookieJar();

		$cookies->queue($cookies->forget($this->getRecallerName()));

		if (! is_null($user)) {
			$this->refreshRememberToken($this->user);
		}

		if (isset($this->events)) {
			$this->events->fire('auth.logout', array($user));
		}

		// Reset the instance information.
		$this->user = null;

		$this->loggedOut = true;
	}

	/**
	 * Refresh the "Remember me" Token for the User.
	 *
	 * @param  \Mini\Auth\Contracts\UserInterface $user
	 * @return void
	 */
	protected function refreshRememberToken(UserInterface $user)
	{
		$token = Str::random(100);

		//
		$user->setRememberToken($token);

		$user->save();
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return \Mini\Auth\Contracts\UserInterface|null
	 */
	public function retrieveUser(array $credentials)
	{
		$model = $this->createModel();

		//
		$query = $model->newQuery();

		foreach ($credentials as $key => $value) {
			if (! Str::contains($key, 'password')) {
				$query->where($key, $value);
			}
		}

		return $query->first();
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Mini\Auth\Contracts\UserInterface|null
	 */
	public function retrieveUserById($identifier)
	{
		$model = $this->createModel();

		return $model->newQuery()->find($identifier);
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string  $token
	 * @return \Mini\Auth\Contracts\UserInterface|null
	 */
	protected function retrieveUserByToken($identifier, $token)
	{
		$model = $this->createModel();

		return $model->newQuery()
			->where($model->getKeyName(), $identifier)
			->where($model->getRememberTokenName(), $token)
			->first();
	}

	/**
	 * Return the currently cached user of the application.
	 *
	 * @return \Mini\Auth\Contracts\UserInterface|null
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the current user of the application.
	 *
	 * @param  \Auth\UserInterface  $user
	 * @return void
	 */
	public function setUser(UserInterface $user)
	{
		$this->user = $user;

		$this->loggedOut = false;

		return $this;
	}

	/**
	 * Get the cookie creator instance used by the guard.
	 *
	 * @return \Cookie\CookieJar
	 *
	 * @throws \RuntimeException
	 */
	public function getCookieJar()
	{
		return $this->cookie;
	}

	/**
	 * Get the encrypter instance used by the guard.
	 *
	 * @return \Mini\Encryption\Encrypter
	 *
	 * @throws \RuntimeException
	 */
	public function getEncrypter()
	{
		return $this->encrypter;
	}

	/**
	 * Get the event dispatcher instance.
	 *
	 * @return \Events\Dispatcher
	 */
	public function getDispatcher()
	{
		return $this->events;
	}

	/**
	 * Get the session store used by the guard.
	 *
	 * @return \Session\Store
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Get the current request instance.
	 *
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->request ?: Request::createFromGlobals();
	}

	/**
	 * Set the current request instance.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request
	 * @return $this
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Get the last user we attempted to authenticate.
	 *
	 * @return \Mini\Auth\Contracts\UserInterface
	 */
	public function getLastAttempted()
	{
		return $this->lastAttempted;
	}

	/**
	 * Get a unique identifier for the auth session value.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'login_' .$this->name .'_' .md5(get_class($this));
	}

	/**
	 * Get the name of the cookie used to store the "recaller".
	 *
	 * @return string
	 */
	public function getRecallerName()
	{
		return PREFIX .'remember_' .$this->name .'_' .md5(get_class($this));
	}

	/**
	 * Determine if the User was authenticated via "remember me" Cookie.
	 *
	 * @return bool
	 */
	public function viaRemember()
	{
		return $this->viaRemember;
	}

	/**
	 * Create a new instance of the model's Query Buider.
	 *
	 * @return \Mini\Database\ORM\Model
	 */
	public function createModel()
	{
		$model = '\\' .ltrim($this->model, '\\');

		return new $model;
	}

}
