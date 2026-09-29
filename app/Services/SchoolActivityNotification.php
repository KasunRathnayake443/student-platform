<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SchoolActivityNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @param array<int, string> $lines */
    public function __construct(
        public int $schoolId,
        public string $subject,
        public string $message,
        public array $lines = [],
        public ?string $actionUrl = null,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $school = School::query()->findOrFail($this->schoolId);
        $mailer = app(SchoolMailTransport::class)->configure($school);

        $message = (new MailMessage)
            ->subject(Str::limit($this->subject, 150))
            ->greeting($this->greeting($notifiable))
            ->line($this->message);

        foreach ($this->lines as $line) {
            $message->line($line);
        }

        if ($this->actionUrl !== null) {
            $message->action('Open student portal', $this->actionUrl);
        }

        return $message
            ->line($this->footer($notifiable))
            ->from($school->email, $school->name)
            ->mailer($mailer);
    }

    /**
     * Parents are notified through an anonymous on-demand notifiable that
     * carries no name, so they get a plain salutation.
     */
    protected function greeting(object $notifiable): string
    {
        $name = $notifiable instanceof User
            ? trim((string) $notifiable->name)
            : '';

        return $name === '' ? 'Hello,' : 'Hello '.$name.',';
    }

    protected function footer(object $notifiable): string
    {
        return $notifiable instanceof User
            ? 'Sign in to your student account to view more details.'
            : 'You are receiving this message as a parent or guardian contact.';
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [];
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300];
    }
}
