<?php

namespace App\Filament\Resources\LearningClasses\RelationManagers;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Students';

    public function table(Table $table): Table
    {
        return $table

            ->columns([

                /*
                |--------------------------------------------------------------------------
                | Student Photo
                |--------------------------------------------------------------------------
                */

                Tables\Columns\ImageColumn::make('profile_photo')

                    ->label('Photo')

                    ->circular(),

                /*
                |--------------------------------------------------------------------------
                | Student Name
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('user.name')

                    ->label('Student Name')

                    ->searchable()

                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Admission Number
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('admission_no')

                    ->label('Admission No'),

                /*
                |--------------------------------------------------------------------------
                | Grade
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make(
                    'currentEnrollment.grade.name'
                )

                    ->label('Grade'),

                /*
                |--------------------------------------------------------------------------
                | School
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make(
                    'currentEnrollment.school.name'
                )

                    ->label('School'),

            ])

            /*
            |--------------------------------------------------------------------------
            | Header Actions
            |--------------------------------------------------------------------------
            */

            ->headerActions([

                /*
                |--------------------------------------------------------------------------
                | Assign Existing Student
                |--------------------------------------------------------------------------
                */

                Action::make('assignStudent')

                    ->label('Assign Existing Student')

                    ->icon('heroicon-o-user-plus')

                    ->form([

                        Select::make('student_enrollment_id')

                            ->label('Student')

                            ->options(function () {

                                $class =
                                    $this->getOwnerRecord();

                                $gradeId =
                                    $class->grade_id;

                                $schoolId =
                                    $class
                                        ->grade
                                        ->school_id;

                                return StudentEnrollment::query()

                                    ->where(
                                        'grade_id',
                                        $gradeId
                                    )

                                    ->where(
                                        'school_id',
                                        $schoolId
                                    )

                                    ->where(
                                        'status',
                                        'active'
                                    )

                                    ->whereNotIn(
                                        'id',
                                        $class
                                            ->enrollments()
                                            ->pluck(
                                                'student_enrollment_id'
                                            )
                                    )

                                    ->with([
                                        'student.user',
                                    ])

                                    ->get()

                                    ->mapWithKeys(
                                        function ($enrollment) {

                                            return [

                                                $enrollment->id => $enrollment
                                                    ->student
                                                    ->user
                                                    ->name

                                                    .' - '.

                                                    $enrollment
                                                        ->student
                                                        ->admission_no,

                                            ];

                                        }
                                    );

                            })

                            ->searchable()

                            ->required(),

                    ])

                    ->action(function (array $data) {

                        $class =
                            $this->getOwnerRecord();

                        $enrollment =
                            StudentEnrollment::findOrFail(
                                $data['student_enrollment_id']
                            );

                        $class

                            ->students()

                            ->syncWithoutDetaching([

                                $enrollment->student_id => [

                                    'student_enrollment_id' => $enrollment->id,

                                ],

                            ]);

                    }),

            ])

            /*
            |--------------------------------------------------------------------------
            | Record Actions
            |--------------------------------------------------------------------------
            */

            ->recordActions([

                /*
                |--------------------------------------------------------------------------
                | View Student
                |--------------------------------------------------------------------------
                */

                Action::make('viewStudent')

                    ->label('View')

                    ->icon('heroicon-o-eye')

                    ->url(function (Student $record) {

                        return StudentResource::getUrl(
                            'view',
                            [
                                'record' => $record,
                            ]
                        );

                    }),

                /*
                |--------------------------------------------------------------------------
                | Remove Student
                |--------------------------------------------------------------------------
                */

                DetachAction::make()

                    ->label('Remove Student'),

            ]);

    }
}
