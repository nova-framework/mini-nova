<?php

namespace Notifications\Events;


class NotificationSent
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
	 * The channel's response.
	 *
	 * @var mixed
	 */
	public $response;


	/**
	 * Create a new event instance.
	 *
	 * @param  mixed  $notifiable
	 * @param  \Notifications\Notification  $notification
	 * @param  mixed  $response
	 * @return void
	 */
	public function __construct($notifiable, $notification, $response = null)
	{
		$this->response = $response;
		$this->notifiable = $notifiable;
		$this->notification = $notification;
	}
}
