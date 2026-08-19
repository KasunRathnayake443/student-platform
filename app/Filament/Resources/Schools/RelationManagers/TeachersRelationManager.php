<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\LearningClass;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    protected static ?string $title = 'Teachers';

    public function table(Table $table): Table
    {

        return $table

            ->recordTitleAttribute('user.name')

            ->columns([

                Tables\Columns\ImageColumn::make('profile_photo')

                    ->label('Profile Photo')

                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')

                    ->label('Teacher Name')

                    ->searchable()

                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')

                    ->label('Email')

                    ->searchable(),

                Tables\Columns\TextColumn::make('employee_no')

                    ->label('Employee No'),

                Tables\Columns\TextColumn::make('phone')

                    ->label('Phone'),

            ])

            ->headerActions([

                /*
                |--------------------------------------------------------------------------
                | Add Existing Teacher
                |--------------------------------------------------------------------------
                */

                Action::make('addTeacher')

                    ->label('Add Existing Teacher')

                    ->icon('heroicon-o-user-plus')

                    ->form([

                        Select::make('teacher_id')

                            ->label('Teacher')

                            ->options(function () {

                                return Teacher::whereDoesntHave(

                                    'schools',

                                    function ($query) {

                                        $query->where(

                                            'school_id',

                                            $this->getOwnerRecord()->id

                                        );

                                    }

                                )
                                    ->with('user')
                                    ->get()
                                    ->pluck(

                                        'user.name',

                                        'id'

                                    );

                            })

                            ->searchable()

                            ->required()

                            ->live(),

                        Select::make('classes')

                            ->label('Teaching Classes')

                            ->multiple()

                            ->options(function () {

                                $school =
                                    $this->getOwnerRecord();

                                return LearningClass::whereHas(

                                    'grade',

                                    function ($query) use ($school) {

                                        $query->where(

                                            'school_id',

                                            $school->id

                                        );

                                    }

                                )
                                    ->with('grade')
                                    ->get()
                                    ->mapWithKeys(function ($class) {

                                        return [

                                            $class->id => 'Grade '
                                            .$class->grade->name
                                            .' → '
                                            .$class->name,

                                        ];

                                    });

                            })

                            ->searchable()

                            ->visible(fn ($get) => filled($get('teacher_id'))

                            ),

                    ])

                    ->action(function (array $data) {

                        $school =
                            $this->getOwnerRecord();

                        $school->teachers()->attach(

                            $data['teacher_id']

                        );

                        if (! empty($data['classes'])) {

                            Teacher::find(

                                $data['teacher_id']

                            )
                                ->classes()
                                ->syncWithoutDetaching(

                                    $data['classes']

                                );

                        }

                    }),

                /*
                |--------------------------------------------------------------------------
                | Create New Teacher
                |--------------------------------------------------------------------------
                */

                Action::make('createTeacher')

                    ->label('Create New Teacher')

                    ->icon('heroicon-o-plus')

                    ->url(fn () => TeacherResource::getUrl(

                        'create',

                        [

                            'school_id' => $this->getOwnerRecord()->id,

                        ]

                    )

                    ),

            ])

            ->recordActions([

                ViewAction::make()

                    ->url(

                        fn ($record) => TeacherResource::getUrl(

                            'view',

                            [

                                'record' => $record,

                            ]

                        )

                    ),

                EditAction::make(),

                DeleteAction::make(),

            ]);

    }
}
