<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('admission_no')
                    ->label('Admission No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('currentEnrollment.school.name')
                    ->label('School'),

                Tables\Columns\TextColumn::make('currentEnrollment.grade.name')
                    ->label('Grade'),

                Tables\Columns\IconColumn::make('user.must_change_password')
                    ->label('Password Pending')
                    ->boolean(),

            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make(),

                DeleteAction::make(),

            ]);
    }
}
