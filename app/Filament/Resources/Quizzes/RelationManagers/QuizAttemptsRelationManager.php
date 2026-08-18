<?php

namespace App\Filament\Resources\Quizzes\RelationManagers;

use App\Models\QuizAttempt;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizAttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $title = 'Student Attempts';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with([
                    'student.user',
                    'answers.question',
                    'answers.selectedOption',
                ]);
            })
            ->columns([

                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.admission_no')
                    ->label('Admission No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attempt_number')
                    ->label('Attempt #')
                    ->sortable(),

                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($state, QuizAttempt $record) => "{$state} / " . ($record->quiz->total_points ?? 0))
                    ->sortable(),

                TextColumn::make('percentage')
                    ->label('Percentage')
                    ->suffix('%')
                    ->sortable(),

                IconColumn::make('is_passed')
                    ->label('Passed')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'success',
                        'in_progress' => 'warning',
                        'time_expired' => 'danger',
                        'abandoned' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Submitted',
                        'in_progress' => 'In Progress',
                        'time_expired' => 'Time Expired',
                        'abandoned' => 'Abandoned',
                        default => ucfirst($state),
                    }),

                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->placeholder('In progress')
                    ->sortable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([

                ViewAction::make()
                    ->infolist([
                        Section::make('Attempt Summary')
                            ->schema([
                                TextEntry::make('student.user.name')->label('Student'),
                                TextEntry::make('student.admission_no')->label('Admission No'),
                                TextEntry::make('attempt_number')->label('Attempt Number'),
                                TextEntry::make('score')->label('Score'),
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
                    ]),

                DeleteAction::make(),

            ]);
    }
}
