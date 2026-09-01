<?php

namespace App\Filament\Resources\LearningClasses\RelationManagers;

use App\Filament\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizzes';

    protected static ?string $title = 'Quizzes';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->with(['teacher.user'])
                    ->withCount(['questions', 'attempts']);
            })
            ->columns([

                TextColumn::make('title')
                    ->label('Quiz Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('teacher.user.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),

                TextColumn::make('total_points')
                    ->label('Total Points')
                    ->suffix(' pts')
                    ->sortable(),

                TextColumn::make('time_limit_minutes')
                    ->label('Time Limit')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}m" : 'Untimed')
                    ->sortable(),

                TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->counts('attempts')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([

                Action::make('createQuiz')
                    ->label('Create Quiz')
                    ->icon('heroicon-o-plus')
                    ->url(
                        fn (): string => QuizResource::getUrl('create', [
                            'learningClassId' => $this->getOwnerRecord()->getKey(),
                        ])
                    )
                    ->openUrlInNewTab(),

            ])
            ->recordActions([

                ViewAction::make()
                    ->url(fn (Quiz $record) => QuizResource::getUrl('view', ['record' => $record])),

                EditAction::make()
                    ->url(fn (Quiz $record) => QuizResource::getUrl('edit', ['record' => $record])),

                DeleteAction::make(),

            ]);
    }
}
