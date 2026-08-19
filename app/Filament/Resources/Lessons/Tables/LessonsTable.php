<?php

namespace App\Filament\Resources\Lessons\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->with([
                        'learningClass.grade.school',
                        'teacher.user',
                    ])
                    ->withCount('attachments');
            })

            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'learningClass.name'
                )
                    ->label('Learning Class')
                    ->searchable(),

                Tables\Columns\TextColumn::make(
                    'learningClass.grade.name'
                )
                    ->label('Grade'),

                Tables\Columns\TextColumn::make(
                    'learningClass.grade.school.name'
                )
                    ->label('School'),

                Tables\Columns\TextColumn::make(
                    'teacher.user.name'
                )
                    ->label('Teacher')
                    ->searchable(),

                Tables\Columns\TextColumn::make(
                    'attachments_count'
                )
                    ->label('Attachments'),

                Tables\Columns\IconColumn::make(
                    'is_published'
                )
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make(
                    'sort_order'
                )
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'created_at'
                )
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

            ])

            ->defaultSort(
                'sort_order'
            )

            ->filters([

                Tables\Filters\TernaryFilter::make(
                    'is_published'
                )
                    ->label('Published'),

            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make(),

                DeleteAction::make(),

            ]);
    }
}
