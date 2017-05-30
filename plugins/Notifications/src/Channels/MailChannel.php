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
	 * Create a new mail channel instance.
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
		$email = $notifiable->routeNotificationFor('mail');

		if (is_null($email)) {
			return;
		}

		$mailMessage = $notification->toMail($notifiable);

		$this->mailer->send($mailMessage->view, $mailMessage->data(), function ($message) use ($notifiable, $notification, $email, $mailMessage)
		{
			$recipients = empty($mailMessage->to) ? $email : $mailMessage->to;

			if (! empty($mailMessage->from)) {
				$message->from($mailMessage->from[0], isset($mailMessage->from[1]) ? $mailMessage->from[1] : null);
			}

			if (is_array($recipients)) {
				$message->bcc($recipients);
			} else {
				$message->to($recipients);
			}

			if ($mailMessage->cc) {
				$message->cc($mailMessage->cc);
			}

			if (! empty($mailMessage->replyTo)) {
				$message->replyTo($mailMessage->replyTo[0], isset($mailMessage->replyTo[1]) ? $mailMessage->replyTo[1] : null);
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

			if (! is_null($mailMessage->priority)) {
				$message->setPriority($mailMessage->priority);
			}
		});
	}
}
