<?php

namespace Mini\Foundation\Console;

use Mini\Console\Command;


class DownCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'down';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Put the application into maintenance mode";

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$basePath = $this->miniNova['path.storage'];

		touch($basePath .DS .'down');

		$this->comment('Application is now in maintenance mode.');
	}

}
