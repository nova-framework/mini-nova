<?php

namespace Backend\Notifications;

use Notifications\Messages\MailMessage;
use Notifications\Notification;


class Sample extends Notification
{

    /**
     * Create a new Sample instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return array('mail', 'database');
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Mini\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return with(new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', 'https://novaframework.com')
            ->line('Thank you for using our application!')
            ->action('Dashboard', site_url('admin/dashboard'))
            ->queued();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return array(
            'message' => 'Just a sample notification.',
            'link'    => site_url('admin/dashboard'),
        );
    }
}
