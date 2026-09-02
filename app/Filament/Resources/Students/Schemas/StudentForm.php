<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Rules\InScope;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\School;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StudentForm
{
    /**
     * @param  array{schoolIds?: array<int, int>|null, passwordRequired?: bool}  $options
     */
    public static function configure(Schema $schema, array $options = []): Schema
    {
        $schoolIds = $options['schoolIds'] ?? null;
        $passwordRequired = (bool) ($options['passwordRequired'] ?? true);

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

        return $schema

            ->components([

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

            ]);

    }
}
