<?php

namespace App\Filament\Teacher\Resources\LearningClasses\RelationManagers;

use App\Filament\Teacher\Resources\Students\StudentResource;
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
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('admission_no')
                    ->label('Admission No'),

                Tables\Columns\TextColumn::make('currentEnrollment.grade.name')
                    ->label('Grade'),

                Tables\Columns\TextColumn::make('currentEnrollment.school.name')
                    ->label('School'),
            ])
            ->headerActions([
                Action::make('assignStudent')
                    ->label('Assign Existing Student')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Select::make('student_id')
                            ->label('Student')
                            ->options(function () {
                                $class = $this->getOwnerRecord();
                                $schoolId = $class->grade->school_id;

                                // Find all active enrollments in the school
                                return StudentEnrollment::query()
                                    ->where('school_id', $schoolId)
                                    ->where('status', 'active')
                                    ->with(['student.user'])
                                    ->get()
                                    // Make sure we only show each student once (they could have multiple enrollments in the same school)
                                    ->unique('student_id')
                                    // Exclude students who are already in THIS class
                                    ->filter(function ($enrollment) use ($class) {
                                        return !$class->students()->where('students.id', $enrollment->student_id)->exists();
                                    })
                                    ->mapWithKeys(function ($enrollment) {
                                        return [
                                            $enrollment->student_id => $enrollment->student->user->name . ' - ' . $enrollment->student->admission_no,
                                        ];
                                    });
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $class = $this->getOwnerRecord();
                        $schoolId = $class->grade->school_id;
                        $gradeId = $class->grade_id;
                        $studentId = $data['student_id'];

                        // Ensure they have an enrollment for this grade
                        $enrollment = StudentEnrollment::firstOrCreate([
                            'student_id' => $studentId,
                            'school_id' => $schoolId,
                            'grade_id' => $gradeId,
                        ], [
                            'status' => 'active',
                            'academic_year' => date('Y'), // Assuming current year
                        ]);

                        // Sync to class
                        $class->students()->syncWithoutDetaching([
                            $studentId => [
                                'student_enrollment_id' => $enrollment->id,
                            ],
                        ]);
                    }),
            ])
            ->recordActions([
                Action::make('viewStudent')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(function (Student $record) {
                        return StudentResource::getUrl('view', ['record' => $record]);
                    }),

                DetachAction::make()
                    ->label('Remove Student'),
            ]);
    }
}
