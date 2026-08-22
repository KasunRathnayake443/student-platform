<?php

namespace App\Filament\Teacher\Resources\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignmentSubmissions';

    protected static ?string $title = 'Assignments';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $teacher = auth()->user()->teacher;
                return $query->whereHas('assignment.learningClass.teachers', function ($q) use ($teacher) {
                    $q->where('teachers.id', $teacher?->id);
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('assignment.title')
                    ->label('Assignment'),
                
                Tables\Columns\TextColumn::make('assignment.learningClass.name')
                    ->label('Class'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'graded',
                        'danger' => 'late',
                    ]),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($record) => $record->score ? "{$record->score} / {$record->assignment->max_score}" : '-'),
                
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                // Teacher can perhaps View it, but view page might not exist in Teacher panel yet.
                // We'll leave it as view-only in the table.
            ])
            ->bulkActions([
                //
            ]);
    }
}
