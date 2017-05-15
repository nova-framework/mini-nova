<?php

namespace Mini\Console;

use Mini\Container\Container;
use Mini\Console\Command;
use Mini\Events\Dispatcher;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

use Exception;


class Application extends SymfonyApplication
{
	/**
	 * The exception handler instance.
	 *
	 * @var \Mini\Exception\Handler
	 */
	protected $exceptionHandler;

	/**
	 * The Nova application instance.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $miniNova;

	/**
	 * The output from the previous command.
	 *
	 * @var \Symfony\Component\Console\Output\BufferedOutput
	 */
	protected $lastOutput;


	/**
	 * Create a new Artisan console application.
	 *
	 * @param  \Illuminate\Contracts\Container\Container  $laravel
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @param  string  $version
	 * @return void
	 */
	public function __construct(Container $miniNova, Dispatcher $events, $version)
	{
		parent::__construct('Mini-Nova Framework', $version);

		$this->miniNova = $miniNova;

		$this->setAutoExit(false);
		$this->setCatchExceptions(false);

		$events->fire('forge.start', array($this));
	}

	/**
	 * Run an Nova console command by name.
	 *
	 * @param  string  $command
	 * @param  array   $parameters
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return void
	 */
	public function call($command, array $parameters = array(), OutputInterface $output = null)
	{
		$parameters['command'] = $command;

		//
		$this->lastOutput = new BufferedOutput;

		$this->setCatchExceptions(false);

		$result = $this->run(new ArrayInput($parameters), $this->lastOutput);

		$this->setCatchExceptions(true);

		return $result;
	}

	/**
	 * Get the output for the last run command.
	 *
	 * @return string
	 */
	public function output()
	{
		return $this->lastOutput ? $this->lastOutput->fetch() : '';
	}

	/**
	 * Add a command to the console.
	 *
	 * @param  \Symfony\Component\Console\Command\Command  $command
	 * @return \Symfony\Component\Console\Command\Command
	 */
	public function add(SymfonyCommand $command)
	{
		if ($command instanceof Command) {
			$command->setMiniNova($this->miniNova);
		}

		return $this->addToParent($command);
	}

	/**
	 * Add the command to the parent instance.
	 *
	 * @param  \Symfony\Component\Console\Command\Command  $command
	 * @return \Symfony\Component\Console\Command\Command
	 */
	protected function addToParent(SymfonyCommand $command)
	{
		return parent::add($command);
	}

	/**
	 * Add a command, resolving through the application.
	 *
	 * @param  string  $command
	 * @return \Symfony\Component\Console\Command\Command
	 */
	public function resolve($command)
	{
		return $this->add($this->miniNova[$command]);
	}

	/**
	 * Resolve an array of commands through the application.
	 *
	 * @param  array|mixed  $commands
	 * @return void
	 */
	public function resolveCommands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();

		foreach ($commands as $command)  {
			$this->resolve($command);
		}
	}

	/**
	 * Get the default input definitions for the applications.
	 *
	 * @return \Symfony\Component\Console\Input\InputDefinition
	 */
	protected function getDefaultInputDefinition()
	{
		$definition = parent::getDefaultInputDefinition();

		$definition->addOption($this->getEnvironmentOption());

		return $definition;
	}

	/**
	 * Get the global environment option for the definition.
	 *
	 * @return \Symfony\Component\Console\Input\InputOption
	 */
	protected function getEnvironmentOption()
	{
		$message = 'The environment the command should run under.';

		return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
	}

	/**
	 * Set the Laravel application instance.
	 *
	 * @param  \Mini\Foundation\Application  $miniNova
	 * @return $this
	 */
	public function setMiniNova($miniNova)
	{
		$this->miniNova = $miniNova;

		return $this;
	}

	/**
	 * Set whether the Console app should auto-exit when done.
	 *
	 * @param  bool  $boolean
	 * @return $this
	 */
	public function setAutoExit($boolean)
	{
		parent::setAutoExit($boolean);

		return $this;
	}

}
