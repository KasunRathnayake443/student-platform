<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformTestEmailNotification extends Notification
{
    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fromName = (string) config('mail.from.name', 'Student Platform');

        return (new MailMessage)
            ->subject('Platform email test')
            ->greeting('Platform email settings test')
            ->line('This is a test email sent from '.$fromName.'.')
            ->line('If you received this message, the platform email settings are working.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
