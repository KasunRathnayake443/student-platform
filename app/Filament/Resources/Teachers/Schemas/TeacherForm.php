<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Models\LearningClass;
use App\Models\School;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema

            ->components([

                /*
                |--------------------------------------------------------------------------
                | User Account
                |--------------------------------------------------------------------------
                */

                FileUpload::make('profile_photo')

                    ->label('Profile Picture')

                    ->image()

                    ->directory('teachers/profile')

                    ->imageEditor()

                    ->avatar()

                    ->nullable(),

                TextInput::make('name')

                    ->label('Teacher Name')

                    ->required(),

                TextInput::make('email')

                    ->label('Email')

                    ->email()

                    ->required(),

                TextInput::make('password')

                    ->label('Password')

                    ->password()

                    ->required(fn ($record) => ! $record),

                /*
                |--------------------------------------------------------------------------
                | Teacher Information
                |--------------------------------------------------------------------------
                */

                TextInput::make('employee_no')

                    ->label('Employee Number')

                    ->unique(
                        'teachers',
                        'employee_no',
                        ignoreRecord: true
                    ),

                TextInput::make('phone')

                    ->label('Phone')

                    ->tel(),

                Textarea::make('address')

                    ->label('Address')

                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | School Assignment
                |--------------------------------------------------------------------------
                */

                Select::make('schools')
                    ->label('Assigned Schools')
                    ->multiple()
                    ->options(
                        School::where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->default(fn () => request()->has('school_id')

                            ? [
                                request()->get('school_id'),
                            ]

                            : []

                    )
                    ->disabled(fn () => request()->has('school_id')

                    )
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->live(),

                /*
                |--------------------------------------------------------------------------
                | Class Assignment
                |--------------------------------------------------------------------------
                */

                CheckboxList::make('classes')

                    ->label('Teaching Classes')

                    ->options(function ($get) {

                        $schools =
                            $get('schools');

                        if (! $schools) {

                            return [];

                        }

                        return LearningClass::whereHas(

                            'grade',

                            function ($query) use ($schools) {

                                $query->whereIn(

                                    'school_id',

                                    $schools

                                );

                            }

                        )
                            ->with('grade.school')
                            ->get()
                            ->mapWithKeys(function ($class) {

                                return [

                                    $class->id => $class->grade->school->name
                                    .' → Grade '
                                    .$class->grade->name
                                    .' → '
                                    .$class->name,

                                ];

                            });

                    })

                    ->columns(1)

                    ->searchable()

                    ->visible(fn ($get) => filled($get('schools'))

                    ),

            ]);

    }
}
