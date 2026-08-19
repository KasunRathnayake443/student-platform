<?php

namespace App\Filament\Resources\Schools\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(
                        url('/images/default-school.png')
                    ),

                Tables\Columns\TextColumn::make('name')
                    ->label('School Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grades_count')
                    ->counts('grades')
                    ->label('Grades')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('classes_count')
                    ->counts('classes')
                    ->label('Classes')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable(),

            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),

            ])
            ->recordActions([

                ViewAction::make(),

                EditAction::make(),

            ]);
    }
}
