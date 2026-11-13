<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Rules\InScope;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\School;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    /**
     * @param  array{schoolIds?: array<int, int>|null, passwordRequired?: bool, manageParentAccount?: bool}  $options
     */
    public static function configure(Schema $schema, array $options = []): Schema
    {
        $schoolIds = $options['schoolIds'] ?? null;
        $passwordRequired = (bool) ($options['passwordRequired'] ?? true);
        $manageParentAccount = (bool) ($options['manageParentAccount'] ?? false);

        $gradeIds = $schoolIds === null
            ? null
            : Grade::query()
                ->whereIn('school_id', $schoolIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

        $classIds = $schoolIds === null
            ? null
            : LearningClass::query()
                ->whereHas('grade', fn ($query) => $query->whereIn('school_id', $schoolIds))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

        $components = [

            FileUpload::make('profile_photo')

                ->label('Profile Picture')

                ->image()

                ->directory('students/profile')

                ->imageEditor()

                ->avatar(),

            TextInput::make('name')

                ->label('Student Name')

                ->required()

                ->maxLength(255),

            TextInput::make('email')

                ->label('Email Address')

                ->email()

                ->required(),

            TextInput::make('password')

                ->label('Password')

                ->password()

                ->helperText(
                    $passwordRequired ? null : 'Leave blank to auto-generate a secure password.'
                )

                ->required(fn ($context) => $context === 'create' && $passwordRequired),

            TextInput::make('admission_no')

                ->label('Admission Number')

                ->required()

                ->unique(
                    'students',
                    'admission_no',
                    ignoreRecord: true
                ),

            DatePicker::make('date_of_birth')
                ->label('Date of Birth'),

            Select::make('gender')

                ->options([

                    'male' => 'Male',

                    'female' => 'Female',

                    'other' => 'Other',

                ]),

            TextInput::make('phone')

                ->label('Student Phone')

                ->tel(),

            Textarea::make('address')

                ->label('Address')

                ->columnSpanFull(),

            TextInput::make('parent_name')
                ->label('Parent Name'),

            TextInput::make('parent_phone')

                ->label('Parent Phone')

                ->tel(),

            TextInput::make('parent_email')

                ->label('Parent Email')

                ->email()

                ->maxLength(255)

                ->helperText('Optional. Used to copy teacher emails to the parent or guardian.'),

            Toggle::make('assign_school')

                ->label('Assign Student To School')

                ->default(
                    fn () => request()->has('school_id')
                )

                ->live(),

            Select::make('schools')

                ->label('Schools')

                ->multiple()

                ->options(

                    School::where(
                        'is_active',
                        true
                    )
                        ->when($schoolIds, fn ($query) => $query->whereIn('id', $schoolIds))
                        ->pluck(
                            'name',
                            'id'
                        )

                )

                ->default(function () use ($schoolIds) {

                    $requested = request()->get('school_id');

                    if ($requested && ($schoolIds === null || in_array((int) $requested, $schoolIds, true))) {

                        return [
                            $requested,
                        ];

                    }

                    return [];

                })

                ->searchable()

                ->preload()

                ->rules([
                    new InScope($schoolIds, 'One or more selected schools are outside your assigned schools.'),
                ])

                ->live()

                ->visible(
                    fn ($get) => $get('assign_school')
                ),

            Select::make('grades')

                ->label('Grades')

                ->multiple()

                ->options(function ($get) {

                    if (! $get('schools')) {

                        return [];

                    }

                    return Grade::whereIn(

                        'school_id',

                        $get('schools')

                    )
                        ->where(
                            'is_active',
                            true
                        )
                        ->get()
                        ->mapWithKeys(fn ($grade) => [

                            $grade->id => $grade->school->name
                            .' → Grade '
                            .$grade->name,

                        ]);

                })

                ->searchable()

                ->preload()

                ->rules([
                    new InScope($gradeIds, 'One or more selected grades are outside your assigned schools.'),
                ])

                ->live()

                ->visible(
                    fn ($get) => $get('assign_school')
                ),

            Select::make('classes')

                ->label('Learning Classes')

                ->multiple()

                ->options(function ($get) {

                    if (! $get('grades')) {

                        return [];

                    }

                    return LearningClass::whereIn(

                        'grade_id',

                        $get('grades')

                    )
                        ->where(
                            'is_active',
                            true
                        )
                        ->with([
                            'grade.school',
                        ])
                        ->get()
                        ->mapWithKeys(fn ($class) => [

                            $class->id => $class->grade->school->name
                            .' → Grade '
                            .$class->grade->name
                            .' → '
                            .$class->name,

                        ]);

                })

                ->searchable()

                ->preload()

                ->rules([
                    new InScope($classIds, 'One or more selected classes are outside your assigned schools.'),
                ])

                ->visible(
                    fn ($get) => $get('assign_school')
                ),

            TextInput::make('academic_year')

                ->label('Academic Year')

                ->default(
                    date('Y')
                )

                ->visible(
                    fn ($get) => $get('assign_school')
                ),

            Select::make('status')

                ->label('Enrollment Status')

                ->options([

                    'active' => 'Active',

                    'inactive' => 'Inactive',

                ])

                ->default('active')

                ->visible(
                    fn ($get) => $get('assign_school')
                ),

        ];

        if ($manageParentAccount) {

            $components[] = Section::make('Parent Account')

                ->description('Create a parent login or link an existing parent account. Parents can view this student\'s quizzes, scores, assignments, lessons, and notifications.')

                ->schema([

                    Select::make('parent_account_mode')

                        ->label('Parent Login')

                        ->options([

                            'none' => 'No parent login',

                            'existing' => 'Link an existing parent account',

                            'new' => 'Create a new parent account',

                        ])

                        ->default('none')

                        ->live()

                        ->required()

                        ->columnSpanFull(),

                    Select::make('parent_id')

                        ->label('Existing Parent Account')

                        ->options(fn () => User::role('parent')

                            ->orderBy('name')

                            ->get()

                            ->mapWithKeys(fn (User $parent) => [

                                $parent->id => $parent->name.' ('.$parent->email.')',

                            ])

                            ->all())

                        ->searchable()

                        ->preload()

                        ->required(fn ($get) => $get('parent_account_mode') === 'existing')

                        ->visible(fn ($get) => $get('parent_account_mode') === 'existing')

                        ->exists('users', 'id')

                        ->helperText('A parent can be linked to multiple children from any school.'),

                    TextInput::make('parent_account_name')

                        ->label('Parent Name')

                        ->required(fn ($get) => $get('parent_account_mode') === 'new')

                        ->visible(fn ($get) => $get('parent_account_mode') === 'new')

                        ->maxLength(255),

                    TextInput::make('parent_account_email')

                        ->label('Parent Email Address')

                        ->email()

                        ->required(fn ($get) => $get('parent_account_mode') === 'new')

                        ->visible(fn ($get) => $get('parent_account_mode') === 'new')

                        ->unique(User::class, 'email', ignoreRecord: false)

                        ->validationMessages([

                            'unique' => 'A user with this email already exists.',

                        ])

                        ->maxLength(255),

                    TextInput::make('parent_account_password')

                        ->label('Parent Password')

                        ->password()

                        ->revealable()

                        ->required(fn ($get) => $get('parent_account_mode') === 'new')

                        ->visible(fn ($get) => $get('parent_account_mode') === 'new')

                        ->helperText('The parent can use this password to sign in at /parent/login.'),

                ])

                ->columns(2)

                ->columnSpanFull();

        }

        return $schema->components($components);

    }
}
