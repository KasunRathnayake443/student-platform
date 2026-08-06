<?php

namespace App\Filament\Resources\Teachers\Tables;


use Filament\Tables;
use Filament\Tables\Table;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;


class TeachersTable
{

    public static function configure(Table $table): Table
    {

        return $table

            ->columns([


                Tables\Columns\ImageColumn::make('profile_photo')

                    ->label('Photo')

                    ->circular(),



                Tables\Columns\TextColumn::make('user.name')

                    ->label('Teacher Name')

                    ->searchable()

                    ->sortable(),



                Tables\Columns\TextColumn::make('user.email')

                    ->label('Email')

                    ->searchable(),



                Tables\Columns\TextColumn::make('employee_no')

                    ->label('Employee No'),



                Tables\Columns\TextColumn::make('phone')

                    ->label('Phone'),



            ])


            ->recordActions([


                ViewAction::make(),

                EditAction::make(),

                DeleteAction::make(),


            ]);

    }

}