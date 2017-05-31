<?php

namespace Notifications\Channels;

use Mini\Mail\Mailer;
use Mini\Support\Str;

use Notifications\Notification;


class MailChannel
{
	/**
	 * The mailer implementation.
	 *
	 * @var \Mini\Mail\Mailer
	 */
	protected $mailer;


	/**
	 * Create a new Mail Channel instance.
	 *
	 * @param  \Mini\Mail\Mailer  $mailer
	 * @return void
	 */
	public function __construct(Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * Send the given notification.
	 *
	 * @param  mixed  $notifiable
	 * @param  \Notifications\Notification  $notification
	 * @return void
	 */
	public function send($notifiable, Notification $notification)
	{
		if (is_null($recipient = $notifiable->routeNotificationFor('mail'))) {
			return;
		}

		$mailMessage = $notification->toMail($notifiable);

		//
		$parameters = array($mailMessage->view, $mailMessage->data(), function ($message) use ($notification, $recipient, $mailMessage)
		{
			if (is_array($recipient)) {
				$message->bcc($recipient);
			} else {
				$message->to($recipient);
			}

			$message->subject($mailMessage->subject ?: Str::title(
				Str::snake(class_basename($notification), ' ')
			));

			foreach ($mailMessage->attachments as $attachment) {
				$message->attach($attachment['file'], $attachment['options']);
			}

			foreach ($mailMessage->rawAttachments as $attachment) {
				$message->attachData($attachment['data'], $attachment['name'], $attachment['options']);
			}
		});

		call_user_func_array(array($this->mailer, $mailMessage->queued ? 'queue' : 'send'), $parameters);
	}
}
