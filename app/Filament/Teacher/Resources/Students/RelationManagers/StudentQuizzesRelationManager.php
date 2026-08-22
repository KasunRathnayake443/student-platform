<?php

namespace App\Filament\Teacher\Resources\Students\RelationManagers;

use App\Models\QuizAttempt;
use App\Models\Student;
use App\Services\ClassContextService;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentQuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizAttempts';

    protected static ?string $title = 'Quizzes';

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
                    ->with(['quiz.learningClass', 'student.user', 'answers.question', 'answers.selectedOption'])
                    ->when(
                        $this->scopedClassId(),
                        fn (Builder $q, int $classId) => $q->whereHas(
                            'quiz',
                            fn (Builder $z) => $z->where('learning_class_id', $classId)
                        ),
                        // Fallback: any quiz in a class taught by this teacher
                        fn (Builder $q) => $q->whereHas('quiz.learningClass.teachers', function ($t) {
                            $t->where('teachers.id', auth()->user()->teacher?->id);
                        })
                    );
            })
            ->columns([
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz'),

                Tables\Columns\TextColumn::make('attempt_number')
                    ->label('Attempt #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'submitted' => 'Submitted',
                        'in_progress' => 'In Progress',
                        'time_expired' => 'Time Expired',
                        'abandoned' => 'Abandoned',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'submitted' => 'success',
                        'in_progress' => 'warning',
                        'time_expired' => 'danger',
                        'abandoned' => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(
                        fn ($state, QuizAttempt $record) => $state !== null
                            ? "{$state} / ".($record->quiz->total_points ?? 0)
                            : '-'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('Percentage')
                    ->suffix('%')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_passed')
                    ->label('Passed')
                    ->boolean(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->placeholder('In progress')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('View Result')
                    ->modalHeading(
                        fn (QuizAttempt $record) => 'Quiz Result - '.$record->quiz->title
                    )
                    ->modalWidth('5xl')
                    ->infolist(function (Schema $schema, QuizAttempt $record): Schema {
                        $record->load([
                            'quiz',
                            'answers.question',
                            'answers.selectedOption',
                        ]);

                        return $schema->components([

                            Section::make('Attempt Summary')
                                ->schema([
                                    TextEntry::make('attempt_number')->label('Attempt Number'),
                                    TextEntry::make('score')
                                        ->label('Score')
                                        ->state("{$record->score} / ".($record->quiz->total_points ?? 0)),
                                    TextEntry::make('percentage')->label('Percentage')->suffix('%'),
                                    IconEntry::make('is_passed')->label('Passed')->boolean(),
                                    TextEntry::make('status')->label('Status')->badge(),
                                    TextEntry::make('started_at')->label('Started At')->dateTime(),
                                    TextEntry::make('completed_at')->label('Completed At')->dateTime()->placeholder('In Progress'),
                                ])
                                ->columns(3),

                            Section::make('Submitted Answers')
                                ->schema([
                                    RepeatableEntry::make('answers')
                                        ->label('')
                                        ->schema([
                                            TextEntry::make('question.question_text')
                                                ->label('Question')
                                                ->weight('bold')
                                                ->columnSpanFull(),
                                            TextEntry::make('selectedOption.option_text')
                                                ->label('Selected Answer')
                                                ->placeholder('No answer selected'),
                                            IconEntry::make('is_correct')
                                                ->label('Is Correct')
                                                ->boolean(),
                                            TextEntry::make('points_awarded')
                                                ->label('Points')
                                                ->suffix(' pts'),
                                        ])
                                        ->columns(3)
                                        ->columnSpanFull(),
                                ]),
                        ]);
                    }),
            ]);
    }
}
