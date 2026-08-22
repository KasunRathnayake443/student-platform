<?php

namespace App\Filament\Teacher\Resources\Students\RelationManagers;

use App\Filament\Resources\Assignments\Schemas\AssignmentSubmissionInfolist;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Services\ClassContextService;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignmentSubmissions';

    protected static ?string $title = 'Assignments';

    public ?int $classId = null;

    /**
     * The class context: either set explicitly (tests/embedding) or taken
     * from the profile page URL (?class=). Validated against the
     * authenticated teacher and the student.
     */
    protected function scopedClassId(): ?int
    {
        $student = $this->getOwnerRecord();

        if (! $student instanceof Student) {
            return null;
        }

        return app(ClassContextService::class)
            ->resolveForStudent(
                $this->classId ?? ((int) request()->query('class')),
                $student
            );
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->with(['assignment.learningClass', 'student.user', 'grader.user', 'attachments'])
                    ->when(
                        $this->scopedClassId(),
                        fn (Builder $q, int $classId) => $q->whereHas(
                            'assignment',
                            fn (Builder $a) => $a->where('learning_class_id', $classId)
                        ),
                        // Fallback: any assignment in a class taught by this teacher
                        fn (Builder $q) => $q->whereHas('assignment.learningClass.teachers', function ($t) {
                            $t->where('teachers.id', auth()->user()->teacher?->id);
                        })
                    );
            })
            ->columns([
                Tables\Columns\TextColumn::make('assignment.title')
                    ->label('Assignment'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        'returned' => 'Returned',
                        default => ucfirst((string) $state),
                    })
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'graded',
                        'danger' => 'late',
                    ]),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(
                        fn ($state, AssignmentSubmission $record) => $state !== null
                            ? "{$state} / {$record->assignment->max_score}"
                            : 'Not graded'
                    ),

                Tables\Columns\TextColumn::make('is_late')
                    ->label('Timing')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Late' : 'On Time')
                    ->color(fn ($state) => $state ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('Not submitted')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('View Result')
                    ->modalHeading(
                        fn (AssignmentSubmission $record) => 'Assignment Result - '.$record->student->user->name
                    )
                    ->modalWidth('5xl')
                    ->infolist(function (Schema $schema, AssignmentSubmission $record): Schema {
                        $record->load([
                            'student.user',
                            'attachments',
                            'grader.user',
                            'assignment',
                        ]);

                        return AssignmentSubmissionInfolist::configure($schema, $record);
                    }),
            ]);
    }
}
