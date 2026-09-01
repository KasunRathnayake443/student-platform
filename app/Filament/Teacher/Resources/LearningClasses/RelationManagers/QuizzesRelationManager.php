<?php

namespace App\Filament\Teacher\Resources\LearningClasses\RelationManagers;

use App\Filament\Teacher\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizzes';

    protected static ?string $title = 'Quizzes';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['teacher.user', 'teachers.user'])
                ->withCount(['questions', 'attempts']))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Quiz Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignee_names')
                    ->label('Teachers')
                    ->badge()
                    ->state(function (Quiz $record): array {
                        $names = $record->teachers
                            ->map(fn (Teacher $teacher) => $teacher->user->name);

                        if ($names->isEmpty() && $record->teacher) {
                            return [$record->teacher->user->name];
                        }

                        return $names->values()->toArray();
                    }),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Questions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_points')
                    ->label('Total Points')
                    ->suffix(' pts')
                    ->sortable(),

                Tables\Columns\TextColumn::make('time_limit_minutes')
                    ->label('Time Limit')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}m" : 'Untimed')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('createQuiz')
                    ->label('Create Quiz')
                    ->icon('heroicon-o-plus')
                    ->url(
                        fn (): string => QuizResource::getUrl('create', [
                            'learningClassId' => $this->getOwnerRecord()->getKey(),
                        ], panel: 'teacher')
                    )
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(
                        fn (Quiz $record) => QuizResource::getUrl('view', ['record' => $record], panel: 'teacher')
                    ),

                EditAction::make()
                    ->url(
                        fn (Quiz $record) => QuizResource::getUrl('edit', ['record' => $record], panel: 'teacher')
                    ),
            ]);
    }
}
