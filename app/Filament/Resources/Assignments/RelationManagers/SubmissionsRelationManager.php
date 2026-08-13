<?php

namespace App\Filament\Resources\Assignments\RelationManagers;

use App\Models\AssignmentSubmission;
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
use Illuminate\Support\Facades\Storage;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Student Submissions';

    protected static ?string $recordTitleAttribute = 'id';

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')

            ->modifyQueryUsing(
                fn (Builder $query) =>
                    $query->with([
                        'student.user',
                        'attachments',
                        'grader.user',
                        'assignment',
                    ])
            )

            ->columns([

                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) =>
                            match ($state) {
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'graded' => 'Graded',
                                'returned' => 'Returned',
                                default => ucfirst(
                                    (string) $state
                                ),
                            }
                    ),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime(
                        'd M Y, h:i A'
                    )
                    ->placeholder('Not submitted')
                    ->sortable(),

                TextColumn::make('is_late')
                    ->label('Late')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) =>
                            $state
                                ? 'Late'
                                : 'On Time'
                    )
                    ->color(
                        fn ($state) =>
                            $state
                                ? 'danger'
                                : 'success'
                    ),

                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(
                        function (
                            $state,
                            AssignmentSubmission $record
                        ) {
                            if ($state === null) {
                                return 'Not graded';
                            }

                            return $state
                                . ' / '
                                . $record->assignment->max_score;
                        }
                    )
                    ->sortable(),

                TextColumn::make('grader.user.name')
                    ->label('Graded By')
                    ->placeholder('—'),

                TextColumn::make('graded_at')
                    ->label('Graded At')
                    ->dateTime(
                        'd M Y, h:i A'
                    )
                    ->placeholder('—'),
            ])

            ->defaultSort(
                'submitted_at',
                'desc'
            )

            ->actions([

                /*
                |--------------------------------------------------------------------------
                | View Submission
                |--------------------------------------------------------------------------
                */

                ViewAction::make()
                    ->label('View Submission')
                    ->modalHeading(
                        fn (
                            AssignmentSubmission $record
                        ) =>
                            'Submission - '
                            . $record->student->user->name
                    )
                    ->modalWidth('5xl')

                    ->infolist(
                        function (
                            Schema $schema,
                            AssignmentSubmission $record
                        ): Schema {

                            $record->load([
                                'student.user',
                                'attachments',
                                'grader.user',
                                'assignment',
                            ]);

                            return $schema
                                ->components([

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Student Information
                                    |--------------------------------------------------------------------------
                                    */

                                    Section::make(
                                        'Student Information'
                                    )
                                        ->schema([

                                            TextEntry::make(
                                                'student.user.name'
                                            )
                                                ->label(
                                                    'Student'
                                                )
                                                ->state(
                                                    $record
                                                        ->student
                                                        ?->user
                                                        ?->name
                                                ),

                                            TextEntry::make(
                                                'student.admission_no'
                                            )
                                                ->label(
                                                    'Admission No.'
                                                )
                                                ->placeholder(
                                                    'Not available'
                                                ),

                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Submission Information
                                    |--------------------------------------------------------------------------
                                    */

                                    Section::make(
                                        'Submission Information'
                                    )
                                        ->schema([

                                            TextEntry::make(
                                                'status'
                                            )
                                                ->label(
                                                    'Status'
                                                )
                                                ->badge()
                                                ->state(
                                                    match (
                                                        $record->status
                                                    ) {
                                                        'draft' =>
                                                            'Draft',

                                                        'submitted' =>
                                                            'Submitted',

                                                        'graded' =>
                                                            'Graded',

                                                        'returned' =>
                                                            'Returned',

                                                        default =>
                                                            ucfirst(
                                                                (string) $record->status
                                                            ),
                                                    }
                                                ),

                                            TextEntry::make(
                                                'submitted_at'
                                            )
                                                ->label(
                                                    'Submitted At'
                                                )
                                                ->dateTime(
                                                    'd M Y, h:i A'
                                                )
                                                ->state(
                                                    $record->submitted_at
                                                )
                                                ->placeholder(
                                                    'Not submitted'
                                                ),

                                            TextEntry::make(
                                                'is_late'
                                            )
                                                ->label(
                                                    'Submission'
                                                )
                                                ->badge()
                                                ->state(
                                                    $record->is_late
                                                        ? 'Late'
                                                        : 'On Time'
                                                )
                                                ->color(
                                                    $record->is_late
                                                        ? 'danger'
                                                        : 'success'
                                                ),

                                        ])
                                        ->columns(3)
                                        ->columnSpanFull(),

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Student Answer
                                    |--------------------------------------------------------------------------
                                    */

                                    Section::make(
                                        'Student Answer'
                                    )
                                        ->schema([

                                            TextEntry::make(
                                                'content'
                                            )
                                                ->label(
                                                    'Answer'
                                                )
                                                ->html()
                                                ->state(
                                                    $record->content
                                                        ?: 'No written answer was provided.'
                                                )
                                                ->columnSpanFull(),

                                        ])
                                        ->columnSpanFull(),

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Submitted Files
                                    |--------------------------------------------------------------------------
                                    */

                                    Section::make(
                                        'Submitted Files'
                                    )
                                        ->description(
                                            'Files submitted by the student with this assignment.'
                                        )
                                        ->schema([

                                            TextEntry::make(
                                                'submission_files'
                                            )
                                                ->label(
                                                    'Attachments'
                                                )
                                                ->state(
                                                    function () use (
                                                        $record
                                                    ) {

                                                        $attachments =
                                                            $record
                                                                ->attachments;

                                                        if (
                                                            $attachments
                                                                ->isEmpty()
                                                        ) {
                                                            return 'No files were submitted.';
                                                        }

                                                        return $attachments
                                                            ->map(
                                                                function (
                                                                    $attachment
                                                                ) {

                                                                    $url =
                                                                        Storage::disk(
                                                                            'public'
                                                                        )->url(
                                                                            $attachment->file_path
                                                                        );

                                                                    $bytes =
                                                                        (int) (
                                                                            $attachment->file_size
                                                                            ?? 0
                                                                        );

                                                                    if (
                                                                        $bytes >=
                                                                        1048576
                                                                    ) {
                                                                        $size =
                                                                            number_format(
                                                                                $bytes /
                                                                                    1048576,
                                                                                2
                                                                            )
                                                                            . ' MB';
                                                                    } elseif (
                                                                        $bytes >=
                                                                        1024
                                                                    ) {
                                                                        $size =
                                                                            number_format(
                                                                                $bytes /
                                                                                    1024,
                                                                                1
                                                                            )
                                                                            . ' KB';
                                                                    } else {
                                                                        $size =
                                                                            $bytes
                                                                            . ' bytes';
                                                                    }

                                                                    return
                                                                        '<div style="
                                                                            display:flex;
                                                                            align-items:center;
                                                                            justify-content:space-between;
                                                                            gap:16px;
                                                                            padding:12px 14px;
                                                                            margin-bottom:8px;
                                                                            border:1px solid #e5e7eb;
                                                                            border-radius:8px;
                                                                            background:#fafafa;
                                                                        ">
                                                                            <div>
                                                                                <div style="
                                                                                    font-weight:600;
                                                                                    color:#111827;
                                                                                ">
                                                                                    '
                                                                                . e(
                                                                                    $attachment->original_name
                                                                                )
                                                                                . '
                                                                                </div>

                                                                                <div style="
                                                                                    font-size:12px;
                                                                                    color:#6b7280;
                                                                                    margin-top:3px;
                                                                                ">
                                                                                    '
                                                                                . e(
                                                                                    $size
                                                                                )
                                                                                . '
                                                                                </div>
                                                                            </div>

                                                                            <a
                                                                                href="'
                                                                                . e(
                                                                                    $url
                                                                                )
                                                                                . '"
                                                                                target="_blank"
                                                                                rel="noopener noreferrer"
                                                                                style="
                                                                                    display:inline-block;
                                                                                    padding:7px 12px;
                                                                                    border-radius:6px;
                                                                                    background:#111827;
                                                                                    color:white;
                                                                                    text-decoration:none;
                                                                                    font-size:13px;
                                                                                    font-weight:600;
                                                                                "
                                                                            >
                                                                                Download
                                                                            </a>
                                                                        </div>';
                                                                }
                                                            )
                                                            ->implode('');
                                                    }
                                                )
                                                ->html()
                                                ->columnSpanFull(),

                                        ])
                                        ->columnSpanFull(),

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Result
                                    |--------------------------------------------------------------------------
                                    */

                                    Section::make(
                                        'Result'
                                    )
                                        ->schema([

                                            TextEntry::make(
                                                'score'
                                            )
                                                ->label(
                                                    'Score'
                                                )
                                                ->state(
                                                    $record->score !== null
                                                        ? $record->score
                                                            . ' / '
                                                            . $record
                                                                ->assignment
                                                                ->max_score
                                                        : 'Not graded'
                                                ),

                                            TextEntry::make(
                                                'percentage'
                                            )
                                                ->label(
                                                    'Percentage'
                                                )
                                                ->state(
                                                    function () use (
                                                        $record
                                                    ) {

                                                        $percentage =
                                                            $record->percentage();

                                                        return $percentage !== null
                                                            ? $percentage . '%'
                                                            : 'Not graded';
                                                    }
                                                ),

                                            TextEntry::make(
                                                'grader.user.name'
                                            )
                                                ->label(
                                                    'Graded By'
                                                )
                                                ->state(
                                                    $record
                                                        ->grader
                                                        ?->user
                                                        ?->name
                                                )
                                                ->placeholder(
                                                    'Not graded'
                                                ),

                                            TextEntry::make(
                                                'graded_at'
                                            )
                                                ->label(
                                                    'Graded At'
                                                )
                                                ->dateTime(
                                                    'd M Y, h:i A'
                                                )
                                                ->state(
                                                    $record->graded_at
                                                )
                                                ->placeholder(
                                                    'Not graded'
                                                ),

                                            TextEntry::make(
                                                'feedback'
                                            )
                                                ->label(
                                                    'Teacher Feedback'
                                                )
                                                ->html()
                                                ->state(
                                                    $record->feedback
                                                        ?: 'No feedback provided.'
                                                )
                                                ->columnSpanFull(),

                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),

                                ]);
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | Grade
                |--------------------------------------------------------------------------
                */

                Action::make('grade')
                    ->label('Grade')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')

                    ->visible(
                        fn (
                            AssignmentSubmission $record
                        ): bool =>
                            $this->canGrade($record)
                    )

                    ->fillForm(
                        fn (
                            AssignmentSubmission $record
                        ) => [
                            'score' => $record->score,
                            'feedback' => $record->feedback,
                        ]
                    )

                    ->form(
                        fn (
                            AssignmentSubmission $record
                        ): array => [

                            Section::make(
                                'Student Result'
                            )
                                ->schema([

                                    TextInput::make(
                                        'score'
                                    )
                                        ->label(
                                            'Score'
                                        )
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(
                                            $record
                                                ->assignment
                                                ->max_score
                                        )
                                        ->suffix(
                                            ' / '
                                            . $record
                                                ->assignment
                                                ->max_score
                                        )
                                        ->required(),

                                    RichEditor::make(
                                        'feedback'
                                    )
                                        ->label(
                                            'Teacher Feedback'
                                        )
                                        ->columnSpanFull(),

                                ])
                                ->columns(1),

                        ]
                    )

                    ->action(
                        function (
                            AssignmentSubmission $record,
                            array $data
                        ): void {

                            $record->update([

                                'score' =>
                                    $data['score'],

                                'feedback' =>
                                    $data['feedback']
                                    ?? null,

                                'graded_by' =>
                                    $this
                                        ->getCurrentTeacherId(),

                                'graded_at' =>
                                    now(),

                                'status' =>
                                    'graded',
                            ]);
                        }
                    )

                    ->modalHeading(
                        fn (
                            AssignmentSubmission $record
                        ) =>
                            'Grade Submission - '
                            . $record->student->user->name
                    )

                    ->modalSubmitActionLabel(
                        'Save Grade'
                    )

                    ->modalWidth('3xl'),

            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Grading Permission
    |--------------------------------------------------------------------------
    */

    protected function canGrade(
        AssignmentSubmission $submission
    ): bool {

        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $teacher = $user->teacher;

        if (! $teacher) {
            return false;
        }

        return (int) $submission
            ->assignment
            ->teacher_id
            === (int) $teacher->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Current Teacher ID
    |--------------------------------------------------------------------------
    */

    protected function getCurrentTeacherId(): ?int
    {
        return auth()
            ->user()
            ?->teacher
            ?->id;
    }
}