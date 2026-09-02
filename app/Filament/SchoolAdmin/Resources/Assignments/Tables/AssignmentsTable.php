<?php

namespace App\Filament\SchoolAdmin\Resources\Assignments\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->with([
                        'teacher.user',
                        'learningClass.grade.school',
                    ])
                    ->withCount('attachments')
                    ->withCount('submissions');
            })
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Assignment')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('learningClass.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('learningClass.grade.school.name')
                    ->label('School')
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacher.user.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_score')
                    ->label('Marks')
                    ->suffix(' marks')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label('Deadline')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->counts('submissions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attachments_count')
                    ->label('Files')
                    ->counts('attachments')
                    ->sortable(),

                Tables\Columns\IconColumn::make('allow_late_submission')
                    ->label('Late')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
