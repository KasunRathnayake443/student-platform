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

use Filament\Schemas\Schema;


class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                /*
                |--------------------------------------------------------------------------
                | Account Information
                |--------------------------------------------------------------------------
                */


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
                    ->required(),



                /*
                |--------------------------------------------------------------------------
                | Student Information
                |--------------------------------------------------------------------------
                */


                TextInput::make('admission_no')
                    ->label('Admission Number')
                    ->required()
                    ->unique('students', 'admission_no'),



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



                /*
                |--------------------------------------------------------------------------
                | Parent Information
                |--------------------------------------------------------------------------
                */


                TextInput::make('parent_name')
                    ->label('Parent Name'),



                TextInput::make('parent_phone')
                    ->label('Parent Phone')
                    ->tel(),



                /*
                |--------------------------------------------------------------------------
                | Enrollment
                |--------------------------------------------------------------------------
                */


                Toggle::make('assign_school')

                ->label('Assign Student To School')
            
                ->default(fn () => request()->has('school_id'))
            
                ->disabled(fn () => request()->has('school_id'))
            
                ->live(),



                Select::make('school_id')

                ->label('School')
            
                ->default(fn () =>
                    request()->get('school_id')
                )
            
                ->disabled(fn () =>
                    request()->has('school_id')
                )

                    ->options(
                        School::query()
                            ->where('is_active', true)
                            ->pluck('name','id')
                    )

                    ->searchable()

                    ->visible(fn ($get) =>
                        $get('assign_school')
                    )

                    ->live(),




                Select::make('grade_id')

                    ->label('Grade')

                    ->options(function($get){

                        if(!$get('school_id')) {
                            return [];
                        }


                        return Grade::where(
                            'school_id',
                            $get('school_id')
                        )
                        ->where('is_active',true)
                        ->pluck('name','id');

                    })

                    ->searchable()

                    ->visible(fn ($get)=>
                        $get('assign_school')
                    )

                    ->live(),




                Select::make('learning_class_id')

                    ->label('Class')

                    ->options(function($get){

                        if(!$get('grade_id')) {
                            return [];
                        }


                        return LearningClass::where(
                            'grade_id',
                            $get('grade_id')
                        )
                        ->where('is_active',true)
                        ->pluck('name','id');

                    })

                    ->searchable()

                    ->visible(fn ($get)=>
                        $get('assign_school')
                    ),




                TextInput::make('academic_year')

                    ->label('Academic Year')

                    ->default(date('Y'))

                    ->visible(fn ($get)=>
                        $get('assign_school')
                    ),




                Select::make('status')

                    ->label('Enrollment Status')

                    ->options([

                        'active'=>'Active',
                        'inactive'=>'Inactive',

                    ])

                    ->default('active')

                    ->visible(fn ($get)=>
                        $get('assign_school')
                    ),


            ]);
    }
}