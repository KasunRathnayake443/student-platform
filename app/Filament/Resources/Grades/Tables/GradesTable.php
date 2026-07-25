<?php

namespace App\Filament\Resources\Grades\Tables;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables;
use Filament\Tables\Table;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('school.name')
                    ->label('School')
                    ->searchable(),

                Tables\Columns\TextColumn::make('learning_classes_count')
                    ->counts('learningClasses')
                    ->label('Classes'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

            ])

            ->filters([
                //
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}