<?php

namespace App\Filament\Resources\LearningClasses\Tables;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables;
use Filament\Tables\Table;

class LearningClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable(),

                Tables\Columns\TextColumn::make('grade.school.name')
                    ->label('School')
                    ->searchable(),

                Tables\Columns\TextColumn::make('medium')
                    ->label('Medium'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}