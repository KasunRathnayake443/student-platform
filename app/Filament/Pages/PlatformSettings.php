<?php

namespace App\Filament\Pages;

use App\Models\ContactSubmission;
use App\Models\PlatformSetting;
use App\Notifications\PlatformTestEmailNotification;
use App\Services\PlatformSettings as PlatformSettingsService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification as MailNotifier;
use Livewire\Attributes\Url;
use Throwable;

/**
 * @property-read Schema $form
 */
class PlatformSettings extends Page implements HasTable
{
    use InteractsWithTable;

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

    #[Url(as: 'tab')]
    public string $activeTab = 'settings';

    public function mount(): void
    {
        if (! in_array($this->activeTab, ['settings', 'submissions'], true)) {
            $this->activeTab = 'settings';
        }

        $record = PlatformSetting::settings();

        $this->form->fill($record->only([
            'platform_name',
            'platform_tagline',
            'platform_logo',
            'contact_address',
            'contact_email',
            'contact_phone',
            'facebook_url',
            'youtube_url',
            'tiktok_url',
            'instagram_url',
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

                Section::make('Contact Information')
                    ->description('These details are shown on the landing page contact section. Leave a field blank to hide it from visitors.')
                    ->schema([
                        Textarea::make('contact_address')
                            ->label('Address')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('hello@example.com'),

                        TextInput::make('contact_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('+94 77 123 4567'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Social Media Links')
                    ->description('Add full URLs to show social media buttons on the landing page. Leave a field blank to hide that button.')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://facebook.com/your-page'),

                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/@your-channel'),

                        TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://tiktok.com/@your-account'),

                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://instagram.com/your-account'),
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
                Tabs::make()
                    ->key('platformSettingsTabs')
                    ->livewireProperty('activeTab')
                    ->contained(false)
                    ->tabs([
                        'settings' => Tab::make('Settings')
                            ->id('settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                $this->getFormContentComponent(),
                            ]),
                        'submissions' => Tab::make('Contact Submissions')
                            ->id('submissions')
                            ->icon('heroicon-o-envelope')
                            ->badge(fn (): int => ContactSubmission::query()->count())
                            ->schema([
                                EmbeddedTable::make(),
                            ]),
                    ]),
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
            'contact_address' => $data['contact_address'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'facebook_url' => $data['facebook_url'] ?? null,
            'youtube_url' => $data['youtube_url'] ?? null,
            'tiktok_url' => $data['tiktok_url'] ?? null,
            'instagram_url' => $data['instagram_url'] ?? null,
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

    public function table(Table $table): Table
    {
        return $table
            ->query(ContactSubmission::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->placeholder('—'),

                TextColumn::make('message')
                    ->label('Message')
                    ->limit(60)
                    ->tooltip(fn (ContactSubmission $record): string => $record->message),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Contact Message')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name'),

                                TextEntry::make('email')
                                    ->label('Email'),

                                TextEntry::make('phone')
                                    ->label('Phone')
                                    ->placeholder('Not provided'),

                                TextEntry::make('created_at')
                                    ->label('Submitted')
                                    ->dateTime(),

                                TextEntry::make('message')
                                    ->label('Message')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No contact messages yet')
            ->emptyStateDescription('Messages submitted from the landing page will appear here.');
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
