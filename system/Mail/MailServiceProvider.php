<?php

namespace Mini\Mail;

use Mini\Mail\Transport\LogTransport;
use Mini\Support\ServiceProvider;

use Swift_Mailer;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Swift_SendmailTransport as SendmailTransport;


class MailServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$me = $this;

		$this->app->bindShared('mailer', function($app) use ($me)
		{
			$me->registerSwiftMailer();

			$mailer = new Mailer(
				$app['view'], $app['swift.mailer'], $app['events']
			);

			$this->setMailerDependencies($mailer, $app);

			$from = $app['config']['mail.from'];

			if (is_array($from) && isset($from['address'])) {
				$mailer->alwaysFrom($from['address'], $from['name']);
			}

			$pretend = $app['config']->get('mail.pretend', false);

			$mailer->pretend($pretend);

			return $mailer;
		});
	}

	/**
	 * Set a few dependencies on the mailer instance.
	 *
	 * @param  \Mini\Mail\Mailer  $mailer
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	protected function setMailerDependencies($mailer, $app)
	{
		$mailer->setContainer($app);

		if ($app->bound('log')) {
			$mailer->setLogger($app['log']);
		}
	}

	/**
	 * Register the Swift Mailer instance.
	 *
	 * @return void
	 */
	public function registerSwiftMailer()
	{
		$config = $this->app['config']['mail'];

		$this->registerSwiftTransport($config);

		$this->app['swift.mailer'] = $this->app->share(function($app)
		{
			return new Swift_Mailer($app['swift.transport']);
		});
	}

	/**
	 * Register the Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function registerSwiftTransport($config)
	{
		switch ($config['driver'])
		{
			case 'smtp':
				return $this->registerSmtpTransport($config);

			case 'sendmail':
				return $this->registerSendmailTransport($config);

			case 'mail':
				return $this->registerMailTransport($config);

			case 'log':
				return $this->registerLogTransport($config);

			default:
				throw new \InvalidArgumentException('Invalid mail driver.');
		}
	}

	/**
	 * Register the SMTP Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerSmtpTransport($config)
	{
		$this->app['swift.transport'] = $this->app->share(function($app) use ($config)
		{
			extract($config);

			$transport = SmtpTransport::newInstance($host, $port);

			if (isset($encryption)) {
				$transport->setEncryption($encryption);
			}

			if (isset($username)) {
				$transport->setUsername($username);

				$transport->setPassword($password);
			}

			return $transport;
		});
	}

	/**
	 * Register the Sendmail Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerSendmailTransport($config)
	{
		$this->app['swift.transport'] = $this->app->share(function($app) use ($config)
		{
			return SendmailTransport::newInstance($config['sendmail']);
		});
	}

	/**
	 * Register the Mail Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerMailTransport($config)
	{
		$this->app['swift.transport'] = $this->app->share(function()
		{
			return MailTransport::newInstance();
		});
	}

	/**
	 * Register the "Log" Swift Transport instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	protected function registerLogTransport($config)
	{
		$this->app->bindShared('swift.transport', function($app)
		{
			return new LogTransport($app->make('Psr\Log\LoggerInterface'));
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('mailer', 'swift.mailer', 'swift.transport');
	}

}
