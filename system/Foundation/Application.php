<?php

namespace Mini\Foundation;

use Mini\Config\FileLoader;
use Mini\Container\Container;
use Mini\Events\EventServiceProvider;
use Mini\Foundation\ProviderRepository;
use Mini\Http\Request;
use Mini\Routing\RoutingServiceProvider;
use Mini\Support\Arr;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Closure;


class Application extends Container
{
	/**
	 * The Mini-me framework version.
	 *
	 * @var string
	 */
	const VERSION = '1.0-dev';

	/**
	 * Indicates if the application has "booted".
	 *
	 * @var bool
	 */
	protected $booted = false;

	/**
	 * The array of booting callbacks.
	 *
	 * @var array
	 */
	protected $bootingCallbacks = array();

	/**
	 * The array of booted callbacks.
	 *
	 * @var array
	 */
	protected $bootedCallbacks = array();

	/**
	 * The array of finish callbacks.
	 *
	 * @var array
	 */
	protected $terminatingCallbacks = array();

	/**
	 * All of the registered service providers.
	 *
	 * @var array
	 */
	protected $serviceProviders = array();

	/**
	 * The names of the loaded service providers.
	 *
	 * @var array
	 */
	protected $loadedProviders = array();

	/**
	 * The deferred services and their providers.
	 *
	 * @var array
	 */
	protected $deferredServices = array();


	/**
	 * Create a new Nova application instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->registerBaseBindings();

		$this->registerBaseServiceProviders();

		$this->registerCoreContainerAliases();
	}

	/**
	 * Get the version number of the application.
	 *
	 * @return string
	 */
	public function version()
	{
		return static::VERSION;
	}

	/**
	 * Create a new request instance from the request class.
	 *
	 * @return \Mini\Http\Request
	 */
	protected function createNewRequest()
	{
		return Request::createFromGlobals();
	}

	/**
	 * Register the basic bindings into the container.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return void
	 */
	protected function registerBaseBindings()
	{
		static::setInstance($this);

		$this->instance('app', $this);

		$this->instance('Mini\Container\Container', $this);
	}

	/**
	 * Register all of the base service providers.
	 *
	 * @return void
	 */
	protected function registerBaseServiceProviders()
	{
		foreach (array('Event', 'Routing') as $name) {
			$this->{"register{$name}Provider"}();
		}
	}

	/**
	 * Register the routing service provider.
	 *
	 * @return void
	 */
	protected function registerRoutingProvider()
	{
		$this->register(new RoutingServiceProvider($this));
	}

	/**
	 * Register the event service provider.
	 *
	 * @return void
	 */
	protected function registerEventProvider()
	{
		$this->register(new EventServiceProvider($this));
	}

	/**
	 * Bind the installation paths to the application.
	 *
	 * @param  array  $paths
	 * @return void
	 */
	public function bindInstallPaths(array $paths)
	{
		$this->instance('path', realpath($paths['app']));

		//
		$paths = Arr::except($paths, array('app'));

		foreach ($paths as $key => $value) {
			$this->instance("path.{$key}", realpath($value));
		}
	}

	/**
	 * Register a service provider with the application.
	 *
	 * @param  \Mini\Support\ServiceProvider|string  $provider
	 * @param  array  $options
	 * @param  bool  $force
	 * @return \Mini\Support\ServiceProvider
	 */
	public function register($provider, $options = array(), $force = false)
	{
		if (! is_null($registered = $this->getRegistered($provider)) && ! $force) {
			return $registered;
		}

		if (is_string($provider)) {
			$provider = $this->resolveProviderClass($provider);
		}

		$provider->register();

		foreach ($options as $key => $value) {
			$this[$key] = $value;
		}

		$this->markAsRegistered($provider);

		if ($this->booted) {
			$provider->boot();
		}

		return $provider;
	}

	/**
	 * Get the registered service provider instnace if it exists.
	 *
	 * @param  \Mini\Support\ServiceProvider|string  $provider
	 * @return \Mini\Support\ServiceProvider|null
	 */
	public function getRegistered($provider)
	{
		$name = is_string($provider) ? $provider : get_class($provider);

		if (array_key_exists($name, $this->loadedProviders)) {
			return Arr::first($this->serviceProviders, function($key, $value) use ($name)
			{
				return (get_class($value) == $name);
			});
		}
	}

	/**
	 * Resolve a service provider instance from the class name.
	 *
	 * @param  string  $provider
	 * @return \Mini\Support\ServiceProvider
	 */
	public function resolveProviderClass($provider)
	{
		return new $provider($this);
	}

	/**
	 * Mark the given provider as registered.
	 *
	 * @param  \Mini\Support\ServiceProvider
	 * @return void
	 */
	protected function markAsRegistered($provider)
	{
		$className = get_class($provider);

		$this->serviceProviders[] = $provider;

		$this->loadedProviders[$className] = true;
	}

	/**
	 * Load and boot all of the remaining deferred providers.
	 *
	 * @return void
	 */
	public function loadDeferredProviders()
	{
		foreach ($this->deferredServices as $service => $provider) {
			$this->loadDeferredProvider($service);
		}

		$this->deferredServices = array();
	}

	/**
	 * Load the provider for a deferred service.
	 *
	 * @param  string  $service
	 * @return void
	 */
	protected function loadDeferredProvider($service)
	{
		$provider = $this->deferredServices[$service];

		if (! isset($this->loadedProviders[$provider])) {
			$this->registerDeferredProvider($provider, $service);
		}
	}

	/**
	 * Register a deffered provider and service.
	 *
	 * @param  string  $provider
	 * @param  string  $service
	 * @return void
	 */
	public function registerDeferredProvider($provider, $service = null)
	{
		if (! is_null($service)) {
			unset($this->deferredServices[$service]);
		}

		$this->register($instance = new $provider($this));

		if ( ! $this->booted) {
			$this->booting(function() use ($instance)
			{
				$instance->boot();
			});
		}
	}

	/**
	 * Resolve the given type from the container.
	 *
	 * (Overriding \Mini\Container\Container::make)
	 *
	 * @param  string  $abstract
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function make($abstract)
	{
		if (isset($this->deferredServices[$abstract])) {
			$this->loadDeferredProvider($abstract);
		}

		return parent::make($abstract);
	}

	/**
	 * Determine if the given abstract type has been bound.
	 *
	 * (Overriding Container::bound)
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function bound($abstract)
	{
		return isset($this->deferredServices[$abstract]) || parent::bound($abstract);
	}

	/**
	 * "Extend" an abstract type in the container.
	 *
	 * (Overriding Container::extend)
	 *
	 * @param  string   $abstract
	 * @param  \Closure  $closure
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function extend($abstract, Closure $closure)
	{
		$abstract = $this->getAlias($abstract);

		if (isset($this->deferredServices[$abstract])) {
			$this->loadDeferredProvider($abstract);
		}

		return parent::extend($abstract, $closure);
	}

	/**
	 * Register a "finish" application filter.
	 *
	 * @param  \Closure|string  $callback
	 * @return void
	 */
	public function finish($callback)
	{
		$this->finishCallbacks[] = $callback;
	}

	/**
	 * Determine if the application has booted.
	 *
	 * @return bool
	 */
	public function isBooted()
	{
		return $this->booted;
	}

	/**
	 * Boot the application's service providers.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ($this->booted) {
			return;
		}

		array_walk($this->serviceProviders, function($provider)
		{
			$provider->boot();
		});

		// Boot the Application.
		$this->fireAppCallbacks($this->bootingCallbacks);

		$this->booted = true;

		$this->fireAppCallbacks($this->bootedCallbacks);
	}

	/**
	 * Register a new boot listener.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function booting($callback)
	{
		$this->bootingCallbacks[] = $callback;
	}

	/**
	 * Register a new "booted" listener.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function booted($callback)
	{
		$this->bootedCallbacks[] = $callback;

		if ($this->booted) {
			$this->fireAppCallbacks(array($callback));
		}
	}

	/**
	 * Throw an HttpException with the given data.
	 *
	 * @param  int	 $code
	 * @param  string  $message
	 * @param  array   $headers
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function abort($code, $message = '', array $headers = array())
	{
		if ($code == 404) {
			throw new NotFoundHttpException($message);
		}

		throw new HttpException($code, $message, null, $headers);
	}

	/**
	 * Register a terminating callback with the application.
	 *
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function terminating(Closure $callback)
	{
		$this->terminatingCallbacks[] = $callback;

		return $this;
	}

	/**
	 * Call the "finish" and "shutdown" callbacks assigned to the application.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @param  \Symfony\Component\HttpFoundation\Response  $response
	 * @return void
	 */
	public function terminate(SymfonyRequest $request, SymfonyResponse $response)
	{
		foreach ($this->terminatingCallbacks as $callback) {
			call_user_func($callback, $request, $response);
		}
	}

	/**
	 * Call the booting callbacks for the application.
	 *
	 * @param  array  $callbacks
	 * @return void
	 */
	protected function fireAppCallbacks(array $callbacks)
	{
		foreach ($callbacks as $callback) {
			call_user_func($callback, $this);
		}
	}

	/**
	 * Set the application's deferred services.
	 *
	 * @param  array  $services
	 * @return void
	 */
	public function setDeferredServices(array $services)
	{
		$this->deferredServices = $services;
	}

	/**
	 * Register the core class aliases in the container.
	 *
	 * @return void
	 */
	public function registerCoreContainerAliases()
	{
		$aliases = array(
			'app'			=> array('Mini\Foundation\Application', 'Mini\Container\Container'),
			'log'			=> array('Mini\Log\Writer', 'Psr\Log\LoggerInterface'),
			'config'		=> array('Mini\Config\Repository'),
			'cookie'		=> array('Mini\Cookie\CookieJar'),
			'encrypter'	 	=> array('Mini\Encryption\Encrypter'),
			'router'		=> array('Mini\Routing\Router'),
			'session.store'	=> array('Mini\Session\Store'),
			'view'			=> array('Mini\View\Factory'),
		);

		foreach ($aliases as $key => $aliases) {
			foreach ((array) $aliases as $alias) {
				$this->alias($key, $alias);
			}
		}
	}

	/**
	 * Get the service provider repository instance.
	 *
	 * @return \Mini\Foundation\ProviderRepository
	 */
	public function getProviderRepository()
	{
		$manifest = $this['config']['app.manifest'];

		return new ProviderRepository($manifest);
	}

	/**
	 * Get the configuration loader instance.
	 *
	 * @return \Mini\Config\LoaderInterface
	 */
	public function getConfigLoader()
	{
		return new FileLoader($this['path'] .DS .'Config');
	}
}
