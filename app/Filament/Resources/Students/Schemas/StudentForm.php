<?php

namespace App\Filament\Resources\Students\Schemas;


use App\Models\School;
use App\Models\Grade;
use App\Models\LearningClass;


use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;


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

                    ->required(fn($context) => $context === 'create'),





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

                        'male'=>'Male',

                        'female'=>'Female',

                        'other'=>'Other',

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
                        fn() =>
                        request()->has('school_id')
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

                        ->pluck(
                            'name',
                            'id'
                        )

                    )

                    ->default(

                        fn() =>

                        request()->has('school_id')

                            ? [
                                request()->get('school_id')
                              ]

                            : []

                    )

                    ->searchable()

                    ->preload()

                    ->live()

                    ->visible(
                        fn($get)=>
                        $get('assign_school')
                    ),







                Select::make('grades')

                    ->label('Grades')

                    ->multiple()

                    ->options(function($get){


                        if(!$get('schools')){

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

                        ->mapWithKeys(fn($grade)=>[

                            $grade->id =>

                            $grade->school->name
                            .' → Grade '
                            .$grade->name

                        ]);

                    })


                    ->searchable()

                    ->preload()

                    ->live()

                    ->visible(
                        fn($get)=>
                        $get('assign_school')
                    ),








                Select::make('classes')

                    ->label('Learning Classes')

                    ->multiple()

                    ->options(function($get){


                        if(!$get('grades')){

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
                            'grade.school'
                        ])

                        ->get()

                        ->mapWithKeys(fn($class)=>[

                            $class->id =>

                            $class->grade->school->name
                            .' → Grade '
                            .$class->grade->name
                            .' → '
                            .$class->name

                        ]);

                    })

                    ->searchable()

                    ->preload()

                    ->visible(
                        fn($get)=>
                        $get('assign_school')
                    ),






                TextInput::make('academic_year')

                    ->label('Academic Year')

                    ->default(
                        date('Y')
                    )

                    ->visible(
                        fn($get)=>
                        $get('assign_school')
                    ),





                Select::make('status')

                    ->label('Enrollment Status')

                    ->options([

                        'active'=>'Active',

                        'inactive'=>'Inactive',

                    ])

                    ->default('active')

                    ->visible(
                        fn($get)=>
                        $get('assign_school')
                    ),



            ]);

    }

}   