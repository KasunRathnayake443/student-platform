<?php

namespace App\Filament\Resources\SchoolAdmins\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolAdminsTable
{
    public static function configure(Table $table): Table
    {

        return $table

            ->columns([

                Tables\Columns\ImageColumn::make('profile_photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')

                    ->label('Name')

                    ->searchable()

                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')

                    ->label('Email')

                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')

                    ->label('Phone'),

                Tables\Columns\TextColumn::make('schools_count')

                    ->counts('schools')

                    ->label('Schools'),

                Tables\Columns\TextColumn::make('created_at')

                    ->label('Created')

                    ->date(),

            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make(),

                DeleteAction::make(),

            ]);

    }
}
