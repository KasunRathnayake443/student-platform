<?php

namespace App\Filament\Resources\Schools\Concerns;

use App\Models\School;
use App\Notifications\SchoolTestEmailNotification;
use App\Services\SchoolMailTransport;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as UiNotification;
use Illuminate\Support\Facades\Notification;
use Throwable;

trait SendsSchoolTestEmail
{
    protected function getSendTestEmailAction(): Action
    {
        return Action::make('sendTestEmail')
            ->label('Send Test Email')
            ->icon('heroicon-o-envelope')
            ->color('primary')
            ->modalHeading('Send Test Email')
            ->modalDescription('Save the school SMTP settings before testing. The message is sent using the saved configuration.')
            ->form([
                TextInput::make('recipient')
                    ->label('Recipient Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->default(fn (): string => (string) auth()->user()?->email)
                    ->helperText('The test message is sent immediately and is not stored.'),
            ])
            ->modalSubmitActionLabel('Send Test')
            ->action(function (array $data): void {
                $this->sendSchoolTestEmail((string) ($data['recipient'] ?? ''));
            });
    }

    protected function sendSchoolTestEmail(string $recipient): void
    {
        $recipient = trim($recipient);

        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            UiNotification::make()
                ->title('Enter a valid recipient email.')
                ->warning()
                ->send();

            return;
        }

        $school = $this->getRecord();

        if (! $school instanceof School) {
            UiNotification::make()
                ->title('The school record is unavailable.')
                ->danger()
                ->send();

            return;
        }

        $transport = app(SchoolMailTransport::class);

        if (! $transport->isConfigured($school)) {
            UiNotification::make()
                ->title('Configure and save the SMTP settings before testing.')
                ->warning()
                ->send();

            return;
        }

        try {
            Notification::route('mail', $recipient)->notify(
                new SchoolTestEmailNotification($school->getKey()),
            );

            UiNotification::make()
                ->title('Test email sent.')
                ->body("Sent to {$recipient}.")
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            UiNotification::make()
                ->title('The test email could not be sent.')
                ->body('Check the SMTP settings and try again.')
                ->danger()
                ->send();
        }
    }
}
