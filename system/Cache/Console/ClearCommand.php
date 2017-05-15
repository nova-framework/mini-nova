<?php

namespace Mini\Cache\Console;

use Mini\Console\Command;
use Mini\Cache\CacheManager;
use Mini\Filesystem\Filesystem;


class ClearCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cache:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Flush the Application cache";

	/**
	 * The Cache Manager instance.
	 *
	 * @var \Mini\Cache\CacheManager
	 */
	protected $cache;

	/**
	 * The File System instance.
	 *
	 * @var \Mini\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new Cache Clear Command instance.
	 *
	 * @param  \Mini\Cache\CacheManager  $cache
	 * @param  \Mini\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct(CacheManager $cache, Filesystem $files)
	{
		parent::__construct();

		$this->cache = $cache;
		$this->files = $files;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->cache->flush();

		$this->files->delete($this->miniNova['config']['app.manifest'] .DS .'services.php');

		$this->info('Application cache cleared!');
	}

}
