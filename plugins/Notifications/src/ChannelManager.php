<?php

namespace Mini\Notifications;

use Mini\Database\ORM\Collection as ModelCollection;
use Mini\Database\ORM\Model;
use Mini\Support\Collection;
use Mini\Support\Manager;

use Notifications\Contracts\DispatcherInterface;

use Ramsey\Uuid\Uuid;

use InvalidArgumentException;


class ChannelManager extends Manager implements DispatcherInterface
{
    /**
     * The default channels used to deliver messages.
     *
     * @var array
     */
    protected $defaultChannels = array('mail', 'database');


	/**
	 * Send the given notification to the given notifiable entities.
	 *
	 * @param  \Mini\Support\Collection|array|mixed  $notifiables
	 * @param  mixed  $notification
	 * @return void
	 */
	public function send($notifiables, $notification)
	{
		if ((! $notifiables instanceof Collection) && ! is_array($notifiables)) {
            $notifiables = array($notifiables);
        }

		$original = clone $notification;

		foreach ($notifiables as $notifiable) {
			$notification = clone $original;

			$notification->id = (string) Uuid::uuid4();

			$channels = $notification->via($notifiable);

			if (empty($channels)) {
				continue;
			}

			foreach ($channels as $channel) {
				if (! $this->shouldSendNotification($notifiable, $notification, $channel)) {
					continue;
				}

				$response = $this->driver($channel)->send($notifiable, $notification);

				$this->app->make('events')->fire(
					new Events\NotificationSent($notifiable, $notification, $channel, $response)
				);
			}
		}
	}

	/**
	 * Determines if the notification can be sent.
	 *
	 * @param  mixed  $notifiable
	 * @param  mixed  $notification
	 * @param  string  $channel
	 * @return bool
	 */
	protected function shouldSendNotification($notifiable, $notification, $channel)
	{
		return $this->app->make('events')->until(
			new Events\NotificationSending($notifiable, $notification, $channel)
		) !== false;
	}

	/**
	 * Get a channel instance.
	 *
	 * @param  string|null  $name
	 * @return mixed
	 */
	public function channel($name = null)
	{
		return $this->driver($name);
	}

	/**
	 * Create an instance of the database driver.
	 *
	 * @return \Notifications\Channels\DatabaseChannel
	 */
	protected function createDatabaseDriver()
	{
		return $this->app->make(Channels\DatabaseChannel::class);
	}

	/**
	 * Create an instance of the mail driver.
	 *
	 * @return \Notifications\Channels\MailChannel
	 */
	protected function createMailDriver()
	{
		return $this->app->make(Channels\MailChannel::class);
	}

	/**
	 * Create a new driver instance.
	 *
	 * @param  string  $driver
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createDriver($driver)
	{
		try {
			return parent::createDriver($driver);
		}
		catch (InvalidArgumentException $e) {
			if (class_exists($driver)) {
				return $this->app->make($driver);
			}

			throw $e;
		}
	}

	/**
	 * Get the default channel driver names.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->defaultChannels;
	}

	/**
	 * Get the default channel driver names.
	 *
	 * @return string
	 */
	public function deliversVia()
	{
		return $this->getDefaultDriver();
	}

	/**
	 * Set the default channel driver name.
	 *
	 * @param  array|string  $channel
	 * @return void
	 */
	public function deliverVia($channels)
	{
		$this->defaultChannel = (array) $channels;
	}
}
