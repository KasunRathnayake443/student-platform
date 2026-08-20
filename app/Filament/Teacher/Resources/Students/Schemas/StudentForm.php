<?php

namespace App\Filament\Teacher\Resources\Students\Schemas;

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
    public static function configure(Schema $schema): Schema
    {

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

                    ->required(fn ($context) => $context === 'create'),

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

                    ->options(function () {
                        $teacher = auth()->user()->teacher;
                        return School::where('is_active', true)
                            ->whereHas('learningClasses', function ($q) use ($teacher) {
                                $q->whereHas('teachers', function ($t) use ($teacher) {
                                    $t->where('teachers.id', $teacher?->id);
                                });
                            })
                            ->pluck('name', 'id');
                    })

                    ->default(

                        fn () => request()->has('school_id')

                            ? [
                                request()->get('school_id'),
                            ]

                            : []

                    )

                    ->searchable()

                    ->preload()

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

                        $teacher = auth()->user()->teacher;

                        return Grade::whereIn('school_id', $get('schools'))
                            ->where('is_active', true)
                            ->whereHas('learningClasses', function ($q) use ($teacher) {
                                $q->whereHas('teachers', function ($t) use ($teacher) {
                                    $t->where('teachers.id', $teacher?->id);
                                });
                            })
                            ->get()
                            ->mapWithKeys(fn ($grade) => [
                                $grade->id => $grade->school->name . ' → Grade ' . $grade->name,
                            ]);
                    })

                    ->searchable()

                    ->preload()

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
                        
                        $teacher = auth()->user()->teacher;

                        return LearningClass::whereIn('grade_id', $get('grades'))
                            ->where('is_active', true)
                            ->whereHas('teachers', function ($t) use ($teacher) {
                                $t->where('teachers.id', $teacher?->id);
                            })
                            ->with(['grade.school'])
                            ->get()
                            ->mapWithKeys(fn ($class) => [
                                $class->id => $class->grade->school->name . ' → Grade ' . $class->grade->name . ' → ' . $class->name,
                            ]);
                    })

                    ->searchable()

                    ->preload()

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
