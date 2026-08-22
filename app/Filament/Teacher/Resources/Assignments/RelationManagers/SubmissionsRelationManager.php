<?php

namespace App\Filament\Teacher\Resources\Assignments\RelationManagers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Student Submissions';

    protected static ?string $recordTitleAttribute = 'id';

    /*
    |--------------------------------------------------------------------------
    | Only teachers assigned to the assignment may open this tab
    |--------------------------------------------------------------------------
    */

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return self::teacherIsAssigned($ownerRecord);
    }

    /*
    |--------------------------------------------------------------------------
    | Non-assigned teachers get no submission data at all
    |--------------------------------------------------------------------------
    |
    | The page hides this tab via canViewForRecord(); the table query below
    | also refuses to return any rows as defence in depth.
    |
    */

    protected static function teacherIsAssigned(Model $ownerRecord): bool
    {
        if (! $ownerRecord instanceof Assignment) {
            return false;
        }

        $teacher = auth()->user()?->teacher;

        if (! $teacher instanceof Teacher) {
            return false;
        }

        return $ownerRecord->isAssignedTo($teacher);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(function (Builder $query): Builder {
                /*
                 * Defence in depth: even if this component were rendered,
                 * teachers who are not assigned to the assignment must
                 * never receive any submission data.
                 */
                if (! self::teacherIsAssigned($this->getOwnerRecord())) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->with([
                    'student.user',
                    'attachments',
                    'grader.user',
                    'assignment',
                ]);
            })
            ->columns([

                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => match ($state) {
                            'draft' => 'Draft',
                            'submitted' => 'Submitted',
                            'graded' => 'Graded',
                            'returned' => 'Returned',
                            default => ucfirst((string) $state),
                        }
                    ),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('Not submitted')
                    ->sortable(),

                TextColumn::make('is_late')
                    ->label('Late')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Late' : 'On Time')
                    ->color(fn ($state) => $state ? 'danger' : 'success'),

                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(
                        function ($state, AssignmentSubmission $record) {
                            if ($state === null) {
                                return 'Not graded';
                            }

                            return $state.' / '.$record->assignment->max_score;
                        }
                    )
                    ->sortable(),

                TextColumn::make('grader.user.name')
                    ->label('Graded By')
                    ->placeholder('-'),

                TextColumn::make('graded_at')
                    ->label('Graded At')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('-'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([
                ViewAction::make()
                    ->label('View Submission')
                    ->modalHeading(
                        fn (AssignmentSubmission $record) => 'Submission - '.$record->student->user->name
                    )
                    ->modalWidth('5xl')
                    ->infolist(function (Schema $schema, AssignmentSubmission $record): Schema {
                        $record->load([
                            'student.user',
                            'attachments',
                            'grader.user',
                            'assignment',
                        ]);

                        return $schema
                            ->components([
                                Section::make('Student Information')
                                    ->schema([
                                        TextEntry::make('student.user.name')
                                            ->label('Student')
                                            ->state($record->student?->user?->name),

                                        TextEntry::make('student.admission_no')
                                            ->label('Admission No.')
                                            ->placeholder('Not available'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Section::make('Submission Information')
                                    ->schema([
                                        TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->state(
                                                match ($record->status) {
                                                    'draft' => 'Draft',
                                                    'submitted' => 'Submitted',
                                                    'graded' => 'Graded',
                                                    'returned' => 'Returned',
                                                    default => ucfirst((string) $record->status),
                                                }
                                            ),

                                        TextEntry::make('submitted_at')
                                            ->label('Submitted At')
                                            ->dateTime('d M Y, h:i A')
                                            ->state($record->submitted_at)
                                            ->placeholder('Not submitted'),

                                        TextEntry::make('is_late')
                                            ->label('Submission')
                                            ->badge()
                                            ->state($record->is_late ? 'Late' : 'On Time')
                                            ->color($record->is_late ? 'danger' : 'success'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),

                                Section::make('Student Answer')
                                    ->schema([
                                        TextEntry::make('content')
                                            ->label('Answer')
                                            ->html()
                                            ->state($record->content ?: 'No written answer was provided.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Result')
                                    ->schema([
                                        TextEntry::make('score')
                                            ->label('Score')
                                            ->state(
                                                $record->score !== null
                                                    ? $record->score.' / '.$record->assignment->max_score
                                                    : 'Not graded'
                                            ),

                                        TextEntry::make('percentage')
                                            ->label('Percentage')
                                            ->state(function () use ($record) {
                                                $percentage = $record->percentage();

                                                return $percentage !== null
                                                    ? $percentage.'%'
                                                    : 'Not graded';
                                            }),

                                        TextEntry::make('grader.user.name')
                                            ->label('Graded By')
                                            ->state($record->grader?->user?->name)
                                            ->placeholder('Not graded'),

                                        TextEntry::make('graded_at')
                                            ->label('Graded At')
                                            ->dateTime('d M Y, h:i A')
                                            ->state($record->graded_at)
                                            ->placeholder('Not graded'),

                                        TextEntry::make('feedback')
                                            ->label('Teacher Feedback')
                                            ->html()
                                            ->state($record->feedback ?: 'No feedback provided.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]);
                    }),

                Action::make('grade')
                    ->label('Grade')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(
                        fn (AssignmentSubmission $record): bool => $this->canGrade($record)
                    )
                    ->fillForm(
                        fn (AssignmentSubmission $record) => [
                            'score' => $record->score,
                            'feedback' => $record->feedback,
                        ]
                    )
                    ->form(
                        fn (AssignmentSubmission $record): array => [
                            Section::make('Student Result')
                                ->schema([
                                    TextInput::make('score')
                                        ->label('Score')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue($record->assignment->max_score)
                                        ->suffix(' / '.$record->assignment->max_score)
                                        ->required(),

                                    RichEditor::make('feedback')
                                        ->label('Teacher Feedback')
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]
                    )
                    ->action(
                        function (AssignmentSubmission $record, array $data): void {
                            $record->update([
                                'score' => $data['score'],
                                'feedback' => $data['feedback'] ?? null,
                                'graded_by' => $this->getCurrentTeacherId(),
                                'graded_at' => now(),
                                'status' => 'graded',
                            ]);
                        }
                    )
                    ->modalHeading(
                        fn (AssignmentSubmission $record) => 'Grade Submission - '.$record->student->user->name
                    )
                    ->modalSubmitActionLabel('Save Grade')
                    ->modalWidth('3xl'),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Grading Permission
    |--------------------------------------------------------------------------
    |
    | Only teachers assigned to the assignment (creator or pivot member)
    | may grade its submissions.
    |
    */

    protected function canGrade(AssignmentSubmission $submission): bool
    {
        $assignment = $submission->assignment;

        if (! $assignment instanceof Assignment) {
            return false;
        }

        $teacher = auth()->user()?->teacher;

        if (! $teacher instanceof Teacher) {
            return false;
        }

        return $assignment->isAssignedTo($teacher);
    }

    protected function getCurrentTeacherId(): ?int
    {
        return auth()->user()?->teacher?->id;
    }
}
