<?php

namespace Mini\Cache;

use Closure;
use Mini\Support\Manager;


class CacheManager extends Manager
{

	/**
	 * Create an instance of the file cache driver.
	 *
	 * @return \Mini\Cache\FileStore
	 */
	protected function createFileDriver()
	{
		$path = $this->app['config']['cache.path'];

		return $this->repository(new FileStore($this->app['files'], $path));
	}

	/**
	 * Get the cache "prefix" value.
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->app['config']['cache.prefix'];
	}

	/**
	 * Set the cache "prefix" value.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setPrefix($name)
	{
		$this->app['config']['cache.prefix'] = $name;
	}

	/**
	 * Create a new cache repository with the given implementation.
	 *
	 * @param  \Mini\Cache\StoreInterface  $store
	 * @return \Mini\Cache\Repository
	 */
	protected function repository(StoreInterface $store)
	{
		return new Repository($store);
	}

	/**
	 * Get the default cache driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->app['config']['cache.driver'];
	}

	/**
	 * Set the default cache driver name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultDriver($name)
	{
		$this->app['config']['cache.driver'] = $name;
	}

}
