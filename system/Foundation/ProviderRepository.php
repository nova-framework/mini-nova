<?php

namespace Mini\Foundation;


class ProviderRepository
{
	/**
	 * The application instance.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * The path to the manifest.
	 *
	 * @var string
	 */
	protected $manifestPath;

	/**
	 * Create a new service repository instance.
	 *
	 * @param  string  $manifestPath
	 * @return void
	 */
	public function __construct(Application $app, $manifestPath)
	{
		$this->app = $app;

		$this->manifestPath = $manifestPath;
	}

	/**
	 * Register the application service providers.
	 *
	 * @param  array  $providers
	 * @param  string  $path
	 * @return void
	 */
	public function load(array $providers)
	{
		$manifest = $this->loadManifest();

		if ($this->shouldRecompile($manifest, $providers)) {
			$manifest = $this->compileManifest($providers);
		}

		if ($this->app->runningInConsole()) {
			$manifest['eager'] = $manifest['providers'];
		}

		foreach ($manifest['eager'] as $provider) {
			$this->app->register($this->createProvider($provider));
		}

		$this->app->setDeferredServices($manifest['deferred']);
	}

	/**
	 * Compile the application manifest file.
	 *
	 * @param  array  $providers
	 * @return array
	 */
	protected function compileManifest($providers)
	{
		$manifest = $this->freshManifest($providers);

		foreach ($providers as $provider) {
			$instance = $this->createProvider($provider);

			if ($instance->isDeferred()) {
				foreach ($instance->provides() as $service) {
					$manifest['deferred'][$service] = $provider;
				}
			} else {
				$manifest['eager'][] = $provider;
			}
		}

		return $this->writeManifest($manifest);
	}

	/**
	 * Create a new provider instance.
	 *
	 * @param  string  $provider
	 * @return \Mini\Support\ServiceProvider
	 */
	public function createProvider($provider)
	{
		return new $provider($this->app);
	}

	/**
	 * Determine if the manifest should be compiled.
	 *
	 * @param  array  $manifest
	 * @param  array  $providers
	 * @return bool
	 */
	public function shouldRecompile($manifest, $providers)
	{
		return is_null($manifest) || ($manifest['providers'] != $providers);
	}

	/**
	 * Load the service provider manifest JSON file.
	 *
	 * @return array
	 */
	public function loadManifest()
	{
		$path = $this->manifestPath .DS .'services.php';

		if (file_exists($path)) {
			$manifest = (array) require $path;

			return $manifest;
		}
	}

	/**
	 * Write the service manifest file to disk.
	 *
	 * @param  array  $manifest
	 * @return array
	 */
	public function writeManifest($manifest)
	{
		$path = $this->manifestPath .DS .'services.php';

		$content = "<?php\n\nreturn " .var_export($manifest, true) .";\n";

		file_put_contents($path, $content);

		return $manifest;
	}

	/**
	 * Create a fresh manifest array.
	 *
	 * @param  array  $providers
	 * @return array
	 */
	protected function freshManifest(array $providers)
	{
		list($eager, $deferred) = array(array(), array());

		return compact('providers', 'eager', 'deferred');
	}
}
