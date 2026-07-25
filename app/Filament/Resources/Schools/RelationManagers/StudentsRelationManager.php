<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';


    protected static ?string $title = 'Students';


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')

            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('admission_no')
                    ->label('Admission No')
                    ->searchable(),


                Tables\Columns\TextColumn::make('currentEnrollment.grade.name')
                    ->label('Grade'),


                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone'),


            ])

            ->recordActions([

                ViewAction::make()
                    ->url(fn ($record) =>
                        StudentResource::getUrl('view', [
                            'record' => $record,
                        ])
                    ),


                EditAction::make(),


                DeleteAction::make(),

            ]);
    }
}