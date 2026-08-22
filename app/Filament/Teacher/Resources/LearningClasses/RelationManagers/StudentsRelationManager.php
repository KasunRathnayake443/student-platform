<?php

namespace App\Filament\Teacher\Resources\LearningClasses\RelationManagers;

use App\Filament\Teacher\Resources\Students\StudentResource;
use App\Models\LearningClass;
use App\Models\Student;
use App\Services\ClassContextService;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Students';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'user',
                'currentEnrollment.grade',
                'currentEnrollment.school',
            ]))
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

                                if (! $class instanceof LearningClass) {
                                    return [];
                                }

                                return app(ClassContextService::class)
                                    ->eligibleStudentsQuery($class)
                                    ->get()
                                    ->filter(fn ($enrollment) => $enrollment->student?->user !== null)
                                    ->mapWithKeys(fn ($enrollment) => [
                                        $enrollment->student_id => $enrollment->student->user->name.' - '.$enrollment->student->admission_no,
                                    ]);
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $class = $this->getOwnerRecord();

                        if (! $class instanceof LearningClass) {
                            return;
                        }

                        $enrollment = app(ClassContextService::class)
                            ->eligibleStudentsQuery($class)
                            ->where('student_id', $data['student_id'])
                            ->first();

                        if (! $enrollment) {
                            return;
                        }

                        $class->students()->syncWithoutDetaching([
                            $data['student_id'] => [
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
                        $url = StudentResource::getUrl('view', ['record' => $record], panel: 'teacher');

                        return $url.'?class='.$this->getOwnerRecord()->getKey();
                    }),

                DetachAction::make()
                    ->label('Remove Student'),
            ]);
    }
}
