<?php

namespace App\Filament\Pages;

use App\Models\PlatformSetting;
use App\Models\StudentRegistration;
use App\Services\PlatformSettings as PlatformSettingsService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TagsInput;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read Schema $form
 */
class StudentRegistrations extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.student-registrations';

    protected static ?string $title = 'Student Registrations';

    protected static ?string $slug = 'student-registrations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public string $activeTab = 'submissions';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $record = PlatformSetting::settings();

        $this->form->fill([
            'registration_fee' => $record->registration_fee,
            'bank_name' => $record->bank_name,
            'bank_account_name' => $record->bank_account_name,
            'bank_account_number' => $record->bank_account_number,
            'bank_branch' => $record->bank_branch,
            'ezcash_number' => $record->ezcash_number,
            'ezcash_merchant_alias' => $record->ezcash_merchant_alias,
            'mcash_number' => $record->mcash_number,
            'mcash_merchant_id' => $record->mcash_merchant_id,
            'registration_languages' => $record->registration_languages ?? PlatformSettingsService::registrationLanguages(),
            'registration_grades' => $record->registration_grades ?? PlatformSettingsService::registrationGrades(),
        ]);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['submissions', 'settings'], true) ? $tab : 'submissions';
    }

    /**
     * @return Builder<StudentRegistration>
     */
    public function getTableQuery(): Builder
    {
        return StudentRegistration::query();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Registration Submissions')
            ->description('Students who submitted the registration form and their payment details.')
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('full_name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('institution')
                    ->label('Institution')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade')
                    ->label('Grade')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('language')
                    ->label('Language')
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('referred_by')
                    ->label('Referred By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        StudentRegistration::PAYMENT_BANK_TRANSFER => 'Bank Transfer / Deposit',
                        StudentRegistration::PAYMENT_EZCASH => 'Dialog eZ Cash',
                        StudentRegistration::PAYMENT_MCASH => 'Mobitel mCash',
                        default => $state,
                    }),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('transaction_reference')
                    ->label('Reference / Slip No.')
                    ->searchable()
                    ->copyable()
                    ->limit(30),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StudentRegistration::STATUS_PENDING => 'warning',
                        StudentRegistration::STATUS_REVIEWED => 'info',
                        StudentRegistration::STATUS_COMPLETED => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        StudentRegistration::STATUS_PENDING => 'Pending',
                        StudentRegistration::STATUS_REVIEWED => 'Reviewed',
                        StudentRegistration::STATUS_COMPLETED => 'Completed',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        StudentRegistration::PAYMENT_BANK_TRANSFER => 'Bank Transfer / Deposit',
                        StudentRegistration::PAYMENT_EZCASH => 'Dialog eZ Cash',
                        StudentRegistration::PAYMENT_MCASH => 'Mobitel mCash',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn (StudentRegistration $record): string => $record->full_name)
                    ->modalWidth('lg')
                    ->modalContent(fn (StudentRegistration $record) => view('filament.pages.student-registration-details', ['record' => $record])),

                Action::make('receipt')
                    ->label('Receipt')
                    ->icon('heroicon-m-paper-clip')
                    ->url(fn (StudentRegistration $record): string => Storage::disk('public')->url($record->receipt_path ?? ''))
                    ->openUrlInNewTab()
                    ->visible(fn (StudentRegistration $record): bool => filled($record->receipt_path)),

                Action::make('markReviewed')
                    ->label('Mark reviewed')
                    ->icon('heroicon-m-check')
                    ->color('info')
                    ->visible(fn (StudentRegistration $record): bool => $record->status === StudentRegistration::STATUS_PENDING)
                    ->action(fn (StudentRegistration $record) => $record->update(['status' => StudentRegistration::STATUS_REVIEWED])),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No registrations yet')
            ->emptyStateDescription('Student registrations submitted from the website will appear here.')
            ->paginated([10, 25, 50]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registration Fee')
                    ->description('The amount students are instructed to pay before submitting the registration form.')
                    ->schema([
                        TextInput::make('registration_fee')
                            ->label('Fee Amount (LKR)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000000)
                            ->placeholder('2500')
                            ->helperText('Displayed on the student registration page.'),
                    ])
                    ->columnSpanFull(),

                Section::make('Option A — Bank Transfer / Cash Deposit')
                    ->description('Bank details shown to students on the registration page.')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('Commercial Bank / BOC')
                            ->maxLength(255),

                        TextInput::make('bank_account_name')
                            ->label('Account Name')
                            ->placeholder('BeeSmart (Pvt) Ltd')
                            ->maxLength(255),

                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(255),

                        TextInput::make('bank_branch')
                            ->label('Branch')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Option B — Dialog eZ Cash')
                    ->description('Dial #111# or use the Dialog Pay / WOW app.')
                    ->schema([
                        TextInput::make('ezcash_number')
                            ->label('eZ Cash Number')
                            ->tel()
                            ->placeholder('077 XXX XXXX')
                            ->maxLength(255),

                        TextInput::make('ezcash_merchant_alias')
                            ->label('Merchant Alias')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Option C — Mobitel mCash')
                    ->description('Dial #111# from a Mobitel number or use the mCash app.')
                    ->schema([
                        TextInput::make('mcash_number')
                            ->label('mCash Number')
                            ->tel()
                            ->placeholder('071 XXX XXXX')
                            ->maxLength(255),

                        TextInput::make('mcash_merchant_id')
                            ->label('Merchant ID')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Registration Form Choices')
                    ->description('These options are used in the student registration form. Press Enter after each choice.')
                    ->schema([
                        TagsInput::make('registration_languages')
                            ->label('Language Choices')
                            ->placeholder('Add a language')
                            ->helperText('Defaults to English, සිංහල and தமிழ் when empty.'),

                        TagsInput::make('registration_grades')
                            ->label('Grade Choices')
                            ->placeholder('Add a grade')
                            ->helperText('For example: Grade 1, Grade 2, ... Other.'),
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
            ->id('registration-settings-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save Registration Settings')
                        ->submit('save'),
                ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        PlatformSetting::settings()->update([
            'registration_fee' => $data['registration_fee'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_name' => $data['bank_account_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_branch' => $data['bank_branch'] ?? null,
            'ezcash_number' => $data['ezcash_number'] ?? null,
            'ezcash_merchant_alias' => $data['ezcash_merchant_alias'] ?? null,
            'mcash_number' => $data['mcash_number'] ?? null,
            'mcash_merchant_id' => $data['mcash_merchant_id'] ?? null,
            'registration_languages' => $data['registration_languages'] ?? null,
            'registration_grades' => $data['registration_grades'] ?? null,
        ]);

        PlatformSettingsService::flushCache();

        Notification::make()
            ->title('Registration settings saved.')
            ->success()
            ->send();
    }
}
