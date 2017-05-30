<?php

namespace Notifications;

use Mini\Database\ORM\Collection as ModelCollection;
use Mini\Database\ORM\Model;
use Mini\Foundation\Application;
use Mini\Support\Collection as BaseCollection;

use Notifications\Events\NotificationFailed;
use Notifications\Events\NotificationSending;
use Notifications\Events\NotificationSent;
use Notifications\Models\Notification as NotificationModel;
use Notifications\Notification;

use Ramsey\Uuid\Uuid;


class Dispatcher
{
	/**
	 * @var Mini\Foundation\Application
	 */
	protected $app;


	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Send the given notification to the given notifiable entities.
	 *
	 * @param  \Illuminate\Support\Collection|array|mixed  $notifiables
	 * @param  mixed  $notification
	 * @return void
	 */
	public function send($notifiables, $notification)
	{
		$notifiables = $this->formatNotifiables($notifiables);

		return $this->sendNow($notifiables, $notification);
	}

	/**
	 * Send the given notification immediately.
	 *
	 * @param  \Mini\Support\Collection|array|mixed  $notifiables
	 * @param  mixed  $notification
	 * @return void
	 */
	public function sendNow($notifiables, $notification)
	{
		$notifiables = $this->formatNotifiables($notifiables);

		$original = clone $notification;

		foreach ($notifiables as $notifiable) {
			$notification = clone $original;

			$notificationId = Uuid::uuid4()->toString();

			if (! $notification->id) {
				$notification->id = $notificationId;
			}

			$response = $this->sendNotification($notifiable, $notification);

			$this->app->make('events')->fire(
				new NotificationSent($notifiable, $notification, $channel, $response)
			);
		}
	}

	/**
	 * Send the given notification.
	 *
	 * @param  mixed  $notifiable
	 * @param  \Notifications\Notification  $notification
	 * @return \Mini\Database\Eloquent\Model
	 */
	public function sendNotification($notifiable, Notification $notification)
	{
		return $notifiable->notifications()->create(array(
			'id'		=> $notification->id,
			'type'		=> get_class($notification),
			'data'		=> $notification->toArray(),
			'read_at'	=> null,
		));
	}

	/**
	 * Determines if the notification can be sent.
	 *
	 * @param  mixed  $notifiable
	 * @param  mixed  $notification
	 * @return bool
	 */
	protected function shouldSendNotification($notifiable, $notification)
	{
		$result = $this->app->make('events')->until(
			new NotificationSending($notifiable, $notification)
		);

		return ($result !== false);
	}

	/**
	 * Format the notifiables into a Collection / array if necessary.
	 *
	 * @param  mixed  $notifiables
	 * @return ModelCollection|array
	 */
	protected function formatNotifiables($notifiables)
	{
		if ((! $notifiables instanceof BaseCollection) && ! is_array($notifiables)) {
			return ($notifiables instanceof Model)
				? new ModelCollection(array($notifiables)) : array($notifiables);
		}

		return $notifiables;
	}
}
