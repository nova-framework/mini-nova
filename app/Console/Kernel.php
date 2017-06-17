<?php

namespace App\Console;

use Mini\Console\Scheduling\Schedule;
use Mini\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
	/**
	 * The Forge commands provided by the application.
	 *
	 * @var array
	 */
	protected $commands = array();


	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Mini\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('mailer:queue:flush')
			->everyMinute();
	}
}
