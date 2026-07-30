<?php

namespace App\Filament\Resources\SchoolAdmins\Tables;


use Filament\Tables;
use Filament\Tables\Table;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;



class SchoolAdminsTable
{


    public static function configure(Table $table): Table
    {

        return $table


            ->modifyQueryUsing(function($query){

                return $query->role('school_admin');

            })



            ->columns([



                Tables\Columns\TextColumn::make('name')

                    ->label('Name')

                    ->searchable()

                    ->sortable(),




                Tables\Columns\TextColumn::make('email')

                    ->label('Email')

                    ->searchable(),





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