<?php

namespace App\Filament\Resources\LearningClasses\RelationManagers;

use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
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

            ->columns([

                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee_no')
                    ->label('Employee No'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone'),

            ])

            ->headerActions([

                /*
                |--------------------------------------------------------------------------
                | Assign Existing Teacher
                |--------------------------------------------------------------------------
                */

                Action::make('assignTeacher')

                    ->label('Assign Existing Teacher')

                    ->icon('heroicon-o-user-plus')

                    ->form([

                        Select::make('teacher_id')

                            ->label('Teacher')

                            ->options(function () {

                                $class =
                                    $this->getOwnerRecord();

                                $schoolId =
                                    $class
                                        ->grade
                                        ->school_id;

                                return Teacher::query()

                                    ->whereHas(
                                        'schools',
                                        function ($query) use ($schoolId) {

                                            $query->where(
                                                'schools.id',
                                                $schoolId
                                            );

                                        }
                                    )

                                    ->with('user')

                                    ->get()

                                    ->mapWithKeys(
                                        function ($teacher) {

                                            return [

                                                $teacher->id => $teacher
                                                    ->user
                                                    ->name

                                                    .' - '.

                                                    $teacher
                                                        ->employee_no,

                                            ];

                                        }
                                    );

                            })

                            ->searchable()

                            ->required(),

                    ])

                    ->action(function (array $data) {

                        $this
                            ->getOwnerRecord()
                            ->teachers()
                            ->syncWithoutDetaching([

                                $data['teacher_id'],

                            ]);

                    }),

                /*
                |--------------------------------------------------------------------------
                | Create New Teacher
                |--------------------------------------------------------------------------
                */

                Action::make('createTeacher')

                    ->label('Create New Teacher')

                    ->icon('heroicon-o-plus')

                    ->url(function () {

                        return TeacherResource::getUrl(
                            'create',
                            [

                                'school_id' => $this
                                    ->getOwnerRecord()
                                    ->grade
                                    ->school_id,

                            ]
                        );

                    }),

            ])

            ->recordActions([

                /*
                |--------------------------------------------------------------------------
                | View Teacher
                |--------------------------------------------------------------------------
                */

                Action::make('viewTeacher')

                    ->label('View')

                    ->icon('heroicon-o-eye')

                    ->url(function (Teacher $record) {

                        return TeacherResource::getUrl(
                            'view',
                            [
                                'record' => $record,
                            ]
                        );

                    }),

                /*
                |--------------------------------------------------------------------------
                | Remove Teacher
                |--------------------------------------------------------------------------
                */

                DetachAction::make()

                    ->label('Remove Teacher'),

            ]);
    }
}
