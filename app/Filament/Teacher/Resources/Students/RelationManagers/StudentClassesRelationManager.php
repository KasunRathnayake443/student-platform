<?php

namespace App\Filament\Teacher\Resources\Students\RelationManagers;

use App\Models\LearningClass;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudentClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'classes';

    protected static ?string $title = 'Learning Classes';

    public function table(Table $table): Table
    {

        return $table

            ->columns([

                Tables\Columns\TextColumn::make('name')

                    ->label('Class'),

                Tables\Columns\TextColumn::make('grade.school.name')

                    ->label('School'),

                Tables\Columns\TextColumn::make('grade.name')

                    ->label('Grade'),

                Tables\Columns\TextColumn::make('medium')

                    ->label('Medium'),

            ])

            ->headerActions([

                Action::make('assignClass')

                    ->label('Assign Class')

                    ->icon('heroicon-o-plus')

                    ->form([

                        Select::make('learning_class_id')

                            ->label('Class')

                            ->options(

                                LearningClass::with(
                                    'grade.school'
                                )
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->get()
                                    ->mapWithKeys(function ($class) {

                                        return [

                                            $class->id => $class->grade->school->name
                                            .' → Grade '
                                            .$class->grade->name
                                            .' → '
                                            .$class->name,

                                        ];

                                    })

                            )

                            ->searchable()

                            ->required(),

                    ])

                    ->action(function (array $data) {

                        $student = $this->getOwnerRecord();

                        $class = LearningClass::find(
                            $data['learning_class_id']
                        );

                        if (! $class) {
                            return;
                        }

                        /*
                        |--------------------------------------------------
                        | Find matching enrollment
                        |--------------------------------------------------
                        */

                        $enrollment = $student
                            ->enrollments()
                            ->where('school_id', $class->grade->school_id)
                            ->where('grade_id', $class->grade_id)
                            ->latest()
                            ->first();

                        if (! $enrollment) {

                            throw new \Exception(
                                'Student is not enrolled in this class school/grade.'
                            );

                        }

                        /*
                        |--------------------------------------------------
                        | Attach class with enrollment reference
                        |--------------------------------------------------
                        */

                        $student
                            ->classes()
                            ->syncWithoutDetaching([

                                $class->id => [

                                    'student_enrollment_id' => $enrollment->id,

                                ],

                            ]);

                    }),

            ])

            ->recordActions([

                DetachAction::make(),

            ]);

    }
}
