<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolAdminsRelationManager extends RelationManager
{
    protected static string $relationship = 'users';


    protected static ?string $title = 'School Admins';


    public function table(Table $table): Table
    {
        return $table

            ->modifyQueryUsing(function ($query) {

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