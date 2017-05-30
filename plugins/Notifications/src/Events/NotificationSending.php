<?php

namespace Notifications\Events;


class NotificationSending
{
	/**
	 * The notifiable entity who received the notification.
	 *
	 * @var mixed
	 */
	public $notifiable;

	/**
	 * The notification instance.
	 *
	 * @var \Notifications\Notification
	 */
	public $notification;


	/**
	 * Create a new event instance.
	 *
	 * @param  mixed  $notifiable
	 * @param  \Notifications\Notification  $notification
	 * @return void
	 */
	public function __construct($notifiable, $notification)
	{
		$this->notifiable = $notifiable;
		$this->notification = $notification;
	}
}
