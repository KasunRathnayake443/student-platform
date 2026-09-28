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

    public function toMail(User $notifiable): MailMessage
    {
        $school = School::query()->findOrFail($this->schoolId);
        $mailer = app(SchoolMailTransport::class)->configure($school);

        $message = (new MailMessage)
            ->subject(Str::limit($this->subject, 150))
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->message);

        foreach ($this->lines as $line) {
            $message->line($line);
        }

        if ($this->actionUrl !== null) {
            $message->action('Open student portal', $this->actionUrl);
        }

        return $message
            ->line('Sign in to your student account to view more details.')
            ->from($school->email, $school->name)
            ->mailer($mailer);
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
