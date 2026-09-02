<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Filament\Rules\InScope;
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
    /**
     * @param  array{schoolIds?: array<int, int>|null, passwordRequired?: bool}  $options
     */
    public static function configure(Schema $schema, array $options = []): Schema
    {
        $schoolIds = $options['schoolIds'] ?? null;
        $passwordRequired = (bool) ($options['passwordRequired'] ?? true);

        $classIds = $schoolIds === null
            ? null
            : LearningClass::query()
                ->whereHas('grade', fn ($query) => $query->whereIn('school_id', $schoolIds))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

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

                    ->helperText(
                        $passwordRequired ? null : 'Leave blank to auto-generate a secure password.'
                    )

                    ->required(fn ($record) => ! $record && $passwordRequired),

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
                            ->when($schoolIds, fn ($query) => $query->whereIn('id', $schoolIds))
                            ->pluck('name', 'id')
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
                    ->disabled(fn () => request()->has('school_id')

                    )
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->rules([
                        new InScope($schoolIds, 'One or more selected schools are outside your assigned schools.'),
                    ])
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

                    ->rules([
                        new InScope($classIds, 'One or more selected classes are outside your assigned schools.'),
                    ])

                    ->visible(fn ($get) => filled($get('schools'))

                    ),

            ]);

    }
}
