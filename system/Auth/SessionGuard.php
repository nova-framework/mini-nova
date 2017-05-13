<?php
/**
 * Guard - A simple Authentication Guard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace Mini\Auth;

use Mini\Auth\GuardInterface;
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
	 * The name of the Guard. Typically "session".
	 *
	 * Corresponds to driver name in authentication configuration.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The user we last attempted to retrieve.
	 *
	 * @var \Nova\Auth\UserInterface
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
								Request $request = null)
	{
		$this->name  = $name;
		$this->model = $model;

		$this->session  = $session;
		$this->hasher   = $hasher;
		$this->request  = $request;
	}

	/**
	 * Get the authenticated user.
	 *
	 * @return \stdClass|null
	 */
	public function user()
	{
		if ($this->loggedOut) {
			return null;
		} else if (! is_null($this->user)) {
			return $this->user;
		}

		$user = null;

		$id = $this->session->get($this->getName());

		if (! is_null($id)) {
			$user = $this->retrieveUserById($id);
		}

		$recaller = $this->getRecaller();

		if (is_null($user) && ! is_null($recaller)) {
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
			return;
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
		if ($this->events) {
			$payload = array($credentials, $remember, $login);

			$this->events->fire('auth.attempt', $payload);
		}
	}

	/**
	 * Register an authentication attempt event listener.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function attempting($callback)
	{
		if ($this->events) {
			$this->events->listen('auth.attempt', $callback);
		}
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
	 * @param  \Mini\Auth\UserInterface $user
	 * @param  bool $remember
	 * @return void
	 */
	public function login(UserInterface $user, $remember = false)
	{
		$this->updateSession($user->getAuthIdentifier());

		if ($remember) {
			$rememberToken = $user->getRememberToken();

			if (empty($rememberToken)) {
				$this->refreshRememberToken($user);
			}

			$this->setRecallerCookie($user);
		}

		if (isset($this->events)) {
			$this->events->fire('auth.login', array($user, $remember));
		}

		$this->setUser($user);
	}

	/**
	 * Log the user out.
	 *
	 * @return void
	 */
	public function logout()
	{
		if (! is_null($this->user)) {
			$this->refreshRememberToken($this->user);
		}

		// Destroy the Session and Cookie variables.
		$this->session->forget($this->getName());

		// Create and queue a Forget Cookie.
		$cookie = $this->getCookieJar()->forget($this->getRecallerName());

		$this->getCookieJar()->queue($cookie);

		// Reset the instance information.
		$this->user = null;

		$this->loggedOut = true;
	}

	/**
	 * Log the given user ID into the application.
	 *
	 * @param  mixed  $id
	 * @param  bool   $remember
	 * @return \Nova\Auth\UserInterface
	 */
	public function loginUsingId($id, $remember = false)
	{
		$this->session->put($this->getName(), $id);

		$user = $this->provider->retrieveById($id);

		$this->login($user, $remember);

		return $user;
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return \Nova\Auth\UserInterface|null
	 */
	public function retrieveUser(array $credentials)
	{
		$query = $this->createModel()->newQuery();

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
	 * @return \Nova\Auth\UserInterface|null
	 */
	public function retrieveUserById($identifier)
	{
		return $this->createModel()->newQuery()->find($identifier);
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string  $token
	 * @return \Mini\Auth\UserInterface|null
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
	 * Refresh the "Remember me" Token for the User.
	 *
	 * @param  \Mini\Auth\UserInterface $user
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
	 * Set the recaller Cookie.
	 *
	 * @param  \Mini\Auth\UserInterface $user
	 * @return void
	 */
	protected function setRecallerCookie(UserInterface $user)
	{
		$value = $user->getAuthIdentifier() .'|' .$user->getRememberToken();

		try {
			$value = $this->getEncrypter()->encrypt($value);
		} catch (EncryptException $e) {
			return;
		}

		$cookie = $this->getCookieJar()->forever($this->getRecallerName(), $value);

		$this->getCookieJar()->queue($cookie);
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
	 * Determine if the recaller Cookie is in a valid format.
	 *
	 * @param  string $recaller
	 * @return bool
	 */
	protected function validRecaller($recaller)
	{
		if (is_string($recaller) && (strpos($recaller, '|') !== false)) {
			$segments = explode('|', $recaller);

			return ((count($segments) == 2) && (trim($segments[0]) !== '') && (trim($segments[1]) !== ''));
		}

		return false;
	}

	/**
	 * Get the decrypted Recaller cookie.
	 *
	 * @return string|null
	 */
	protected function getRecaller()
	{
		$cookie = $this->request->cookies->get($this->getRecallerName());

		if (is_null($cookie)) {
			return;
		}

		try {
			return $this->getEncrypter()->decrypt($cookie);
		} catch (DecryptException $e) {
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
	 * Return the currently cached user of the application.
	 *
	 * @return \Nova\Auth\UserInterface|null
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
		if (! isset($this->cookie)) {
			throw new \RuntimeException("Cookie jar has not been set.");
		}

		return $this->cookie;
	}

	/**
	 * Set the cookie creator instance used by the guard.
	 *
	 * @param  \Mini\Cookie\CookieJar  $cookie
	 * @return void
	 */
	public function setCookieJar(CookieJar $cookie)
	{
		$this->cookie = $cookie;
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
		if (! isset($this->encrypter)) {
			throw new \RuntimeException("Encrypter has not been set.");
		}

		return $this->encrypter;
	}

	/**
	 * Set the encrypter instance used by the guard.
	 *
	 * @param  \Mini\Encryption\Encrypter  $encrypter
	 * @return void
	 */
	public function setEncrypter(Encrypter $encrypter)
	{
		$this->encrypter = $encrypter;
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
	 * Set the event dispatcher instance.
	 *
	 * @param  \Nova\Events\Dispatcher
	 * @return void
	 */
	public function setDispatcher(Dispatcher $events)
	{
		$this->events = $events;
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
	 * @return \Mini\Auth\UserInterface
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
		return 'remember_' .$this->name .'_' .md5(get_class($this));
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
	 * Create a new instance of the model.
	 *
	 * @return \Mini\Database\ORM\Model
	 */
	public function createModel()
	{
		$className = '\\' .ltrim($this->model, '\\');

		return new $className;
	}

}
