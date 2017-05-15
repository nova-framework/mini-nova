<?php

namespace Mini\Foundation\Console;

use Mini\Console\Contracts\KernelInterface;
use Mini\Console\Application as ConsoleApplication;
use Mini\Events\Dispatcher;
use Mini\Foundation\Application;

use Exception;
use Throwable;


class Kernel implements KernelInterface
{
	/**
	 * The application instance.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * The event dispatcher implementation.
	 *
	 * @var \Mini\Events\Dispatcher
	 */
	protected $events;

	/**
	 * The forge console instance.
	 *
	 * @var  \Mini\Console\Application
	 */
	protected $forge;

	/**
	 * The Forge commands provided by the application.
	 *
	 * @var array
	 */
	protected $commands = array();

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = array(
        'Mini\Foundation\Bootstrap\LoadConfiguration',
        'Mini\Foundation\Bootstrap\ConfigureLogging',
        'Mini\Foundation\Bootstrap\HandleExceptions',
        'Mini\Foundation\Bootstrap\SetRequestForConsole',
        'Mini\Foundation\Bootstrap\BootApplication',
    );

	/**
	 * Create a new forge command runner instance.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app, Dispatcher $events)
	{
		if (! defined('FORGE_BINARY')) {
			define('FORGE_BINARY', 'forge');
		}

		$this->app = $app;

		$this->events = $events;
	}


    /**
     * Run the console application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        try {
            $this->bootstrap();

            return $this->getForge()->run($input, $output);
        } catch (Exception $e) {
            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        } catch (Throwable $e) {
            $e = new FatalThrowableError($e);

            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        }
    }

    /**
     * Terminate the application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  int  $status
     * @return void
     */
    public function terminate($input, $status)
    {
        $this->app->terminate();
    }

	/**
	 * Run a Forge console command by name.
	 *
	 * @param  string  $command
	 * @param  array  $parameters
	 * @return int
	 */
	public function call($command, array $parameters = array())
	{
		$this->bootstrap();

		return $this->getForge()->call($command, $parameters);
	}

	/**
	 * Get all of the commands registered with the console.
	 *
	 * @return array
	 */
	public function all()
	{
		$this->bootstrap();

		return $this->getForge()->all();
	}

	/**
	 * Get the output for the last run command.
	 *
	 * @return string
	 */
	public function output()
	{
		$this->bootstrap();

		return $this->getForge()->output();
	}

	/**
	 * Get the forge console instance.
	 *
	 * @return \Mini\Console\Application
	 */
	protected function getForge()
	{
		if (isset($this->forge)) {
			return $this->forge;
		}

		$this->forge = new ConsoleApplication($this->app, $this->events, $this->app->version());

		$this->forge->resolveCommands($this->commands);

		return $this->forge;
	}

    /**
     * Bootstrap the application for artisan commands.
     *
     * @return void
     */
    public function bootstrap()
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }

        $this->app->loadDeferredProviders();
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Exception  $e
     * @return void
     */
    protected function reportException(Exception $e)
    {
        $this->getExceptionHandler()->report($e);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Exception  $e
     * @return void
     */
    protected function renderException($output, Exception $e)
    {
        $this->getExceptionHandler()->renderForConsole($output, $e);
    }

	/**
	 * Get the Nova application instance.
	 *
	 * @return \Mini\Foundation\Contracts\ExceptionHandlerInterface
	 */
	public function getExceptionHandler()
	{
		return $this->app->make('Mini\Foundation\Contracts\ExceptionHandlerInterface');
	}
}
