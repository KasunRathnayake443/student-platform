<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachingTeachers';


    protected static ?string $title = 'Teachers';


    public function table(Table $table): Table
    {
        return $table

            ->modifyQueryUsing(function ($query) {

                return $query->role('teacher');

            })


            ->columns([


                Tables\Columns\TextColumn::make('name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->date(),


            ])



            ->recordActions([

                EditAction::make(),

                DeleteAction::make(),

            ]);
    }

    
}