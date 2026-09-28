<?php

namespace App\Notifications;

use App\Models\School;
use App\Services\SchoolMailTransport;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SchoolTestEmailNotification extends Notification
{
    public function __construct(public int $schoolId) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $school = School::query()->findOrFail($this->schoolId);
        $mailer = app(SchoolMailTransport::class)->configure($school);

        return (new MailMessage)
            ->subject(Str::limit('SMTP test - '.$school->name, 150))
            ->greeting('SMTP configuration test')
            ->line('This is a test email sent from '.$school->name.'.')
            ->line('If you received this message, the school SMTP settings are working.')
            ->from($school->email, $school->name)
            ->mailer($mailer);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
