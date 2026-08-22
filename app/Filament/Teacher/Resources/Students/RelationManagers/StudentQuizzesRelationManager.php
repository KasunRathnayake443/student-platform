<?php

namespace App\Filament\Teacher\Resources\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentQuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizAttempts';

    protected static ?string $title = 'Quizzes';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $teacher = auth()->user()->teacher;
                return $query->whereHas('quiz.learningClass.teachers', function ($q) use ($teacher) {
                    $q->where('teachers.id', $teacher?->id);
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz'),

                Tables\Columns\TextColumn::make('quiz.learningClass.name')
                    ->label('Class'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'abandoned',
                    ]),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($record) => $record->score !== null ? "{$record->score} / {$record->quiz->total_marks}" : '-'),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
