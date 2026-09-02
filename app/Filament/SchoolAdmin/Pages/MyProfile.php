<?php

namespace App\Filament\SchoolAdmin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read Schema $form
 */
class MyProfile extends Page
{
    protected string $view = 'filament.school-admin.pages.my-profile';

    protected static ?string $title = 'My Profile';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function getLayout(): string
    {
        return 'filament.school-admin.layouts.app';
    }

    public function mount(): void
    {
        $this->form->fill($this->profileData());
    }

    /**
     * @return array<string, mixed>
     */
    protected function profileData(): array
    {
        $user = auth()->user();
        $schoolAdmin = $user?->schoolAdmin;

        return [
            'profile_photo' => $schoolAdmin?->profile_photo,
            'name' => $user?->name,
            'email' => $user?->email,
            'phone' => $schoolAdmin?->phone,
            'address' => $schoolAdmin?->address,
        ];
    }

    public function form(Schema $schema): Schema
    {
        /*
        |--------------------------------------------------------------------------
        | Editable Profile (mirrors super-admin Edit School Admin)
        | School assignments are managed by super administrators.
        |--------------------------------------------------------------------------
        */

        return $schema
            ->components([
                FileUpload::make('profile_photo')
                    ->label('Profile Picture')
                    ->image()
                    ->avatar()
                    ->directory('school-admins/profile')
                    ->imageEditor(),

                TextInput::make('name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(table: 'users', column: 'email', ignorable: fn () => auth()->user()),

                TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->maxLength(50),

                Textarea::make('address')
                    ->label('Address')
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('new_password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->nullable()
                    ->confirmed()
                    ->rule(Password::defaults()),

                TextInput::make('new_password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable(),
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
            ->label('Save Changes')
            ->submit('save');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();
        $schoolAdmin = $user?->schoolAdmin;

        if (! $user || ! $schoolAdmin) {
            return;
        }

        $userUpdates = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if ($user->email !== $data['email']) {
            $userUpdates['email_verified_at'] = null;
        }

        $user->forceFill($userUpdates)->save();

        $schoolAdmin->update([
            'profile_photo' => $data['profile_photo'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        if (filled($data['new_password'] ?? null)) {
            $user->forceFill([
                'password' => Hash::make($data['new_password']),
                'must_change_password' => false,
            ])->save();
        }

        Notification::make()
            ->title('Profile updated successfully.')
            ->success()
            ->send();
    }
}
