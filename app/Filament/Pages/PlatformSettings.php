<?php

namespace App\Filament\Pages;

use App\Models\PlatformSetting;
use App\Notifications\PlatformTestEmailNotification;
use App\Services\PlatformSettings as PlatformSettingsService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Notification as MailNotifier;
use Throwable;

/**
 * @property-read Schema $form
 */
class PlatformSettings extends Page
{
    protected string $view = 'filament.pages.platform-settings';

    protected static ?string $title = 'Platform Settings';

    protected static ?string $slug = 'platform-settings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $record = PlatformSetting::settings();

        $this->form->fill($record->only([
            'platform_name',
            'platform_tagline',
            'platform_logo',
            'mail_from_address',
            'mail_from_name',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_encryption',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Platform Branding')
                    ->description('Used as the platform name across super admin panels and as the default sender of platform emails.')
                    ->schema([
                        FileUpload::make('platform_logo')
                            ->label('Platform Logo')
                            ->image()
                            ->directory('platform/logo')
                            ->imageEditor()
                            ->nullable()
                            ->helperText('Shown in the sidebar branding. Removes the current logo when cleared.'),

                        TextInput::make('platform_name')
                            ->label('Platform Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('platform_tagline')
                            ->label('Tagline')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Password Reset Email Settings')
                    ->description('These settings are used to send password reset and verification emails to all users. Leave a field blank to keep the value from the .env file (or the previously saved value).')
                    ->schema([
                        TextInput::make('mail_from_address')
                            ->label('From Email Address')
                            ->email()
                            ->maxLength(255)
                            ->helperText('The sender address used on password reset emails.'),

                        TextInput::make('mail_from_name')
                            ->label('From Name')
                            ->maxLength(255),

                        TextInput::make('mail_host')
                            ->label('SMTP Host')
                            ->maxLength(255)
                            ->placeholder('smtp.example.com'),

                        TextInput::make('mail_port')
                            ->label('SMTP Port')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->placeholder('587'),

                        Select::make('mail_encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS (usually port 587)',
                                'ssl' => 'SSL (usually port 465)',
                                'none' => 'None',
                            ])
                            ->placeholder('Use .env setting')
                            ->columnSpanFull(),

                        TextInput::make('mail_username')
                            ->label('SMTP Username')
                            ->maxLength(255),

                        TextInput::make('mail_password')
                            ->label('SMTP Password or App Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->formatStateUsing(fn (): ?string => null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->nullable()
                            ->maxLength(1024)
                            ->helperText('Stored encrypted. Leave blank to keep the current password.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    $this->getSaveFormAction(),
                ]),
            ]);
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Save Settings')
            ->submit('save');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = PlatformSetting::settings();

        $record->update([
            'platform_name' => $data['platform_name'] ?? null,
            'platform_tagline' => $data['platform_tagline'] ?? null,
            'platform_logo' => $data['platform_logo'] ?? null,
            'mail_from_address' => $data['mail_from_address'] ?? null,
            'mail_from_name' => $data['mail_from_name'] ?? null,
            'mail_host' => $data['mail_host'] ?? null,
            'mail_port' => $data['mail_port'] ?? null,
            'mail_username' => $data['mail_username'] ?? null,
            'mail_encryption' => $data['mail_encryption'] ?? null,
        ]);

        if (filled($data['mail_password'] ?? null)) {
            $record->forceFill([
                'mail_password' => $data['mail_password'],
            ])->save();
        }

        PlatformSettingsService::flushCache();
        PlatformSettingsService::apply();

        Notification::make()
            ->title('Platform settings saved.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getSendTestEmailAction(),
        ];
    }

    protected function getSendTestEmailAction(): Action
    {
        return Action::make('sendTestEmail')
            ->label('Send Test Email')
            ->icon('heroicon-o-envelope')
            ->color('primary')
            ->modalHeading('Send Test Email')
            ->modalDescription('The message is sent with the saved platform email settings. Save your changes before testing.')
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
                $this->sendPlatformTestEmail((string) ($data['recipient'] ?? ''));
            });
    }

    protected function sendPlatformTestEmail(string $recipient): void
    {
        $recipient = trim($recipient);

        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            Notification::make()
                ->title('Enter a valid recipient email.')
                ->warning()
                ->send();

            return;
        }

        try {
            MailNotifier::route('mail', $recipient)->notify(
                new PlatformTestEmailNotification,
            );

            Notification::make()
                ->title('Test email sent.')
                ->body("Sent to {$recipient}.")
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('The test email could not be sent.')
                ->body('Check the email settings and try again.')
                ->danger()
                ->send();
        }
    }
}
