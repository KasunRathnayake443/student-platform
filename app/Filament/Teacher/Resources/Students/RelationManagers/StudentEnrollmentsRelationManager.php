<?php

namespace App\Filament\Teacher\Resources\Students\RelationManagers;

use App\Models\Grade;
use App\Models\School;
use App\Models\StudentEnrollment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudentEnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'School Enrollments';

    public function table(Table $table): Table
    {

        return $table

            ->columns([

                Tables\Columns\TextColumn::make('school.name')

                    ->label('School')

                    ->searchable(),

                Tables\Columns\TextColumn::make('grade.name')

                    ->label('Grade'),

                Tables\Columns\TextColumn::make('academic_year')

                    ->label('Year'),

                Tables\Columns\TextColumn::make('status')

                    ->label('Status'),

            ])

            ->headerActions([

                Action::make('addEnrollment')

                    ->label('Add Enrollment')

                    ->icon('heroicon-o-plus')

                    ->form([

                        Select::make('school_id')

                            ->label('School')

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

                            ->searchable()

                            ->required()

                            ->live(),

                        Select::make('grade_id')

                            ->label('Grade')

                            ->options(function ($get) {

                                if (! $get('school_id')) {

                                    return [];

                                }

                                return Grade::where(
                                    'school_id',
                                    $get('school_id')
                                )
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->pluck(
                                        'name',
                                        'id'
                                    );

                            })

                            ->required(),

                        TextInput::make('academic_year')

                            ->default(
                                date('Y')
                            )

                            ->required(),

                    ])

                    ->action(function (array $data) {

                        StudentEnrollment::create([

                            'student_id' => $this->getOwnerRecord()->id,

                            'school_id' => $data['school_id'],

                            'grade_id' => $data['grade_id'],

                            'academic_year' => $data['academic_year'],

                            'status' => 'active',

                        ]);

                    }),

            ])

            ->recordActions([

                EditAction::make(),

                DeleteAction::make(),

            ]);

    }
}
