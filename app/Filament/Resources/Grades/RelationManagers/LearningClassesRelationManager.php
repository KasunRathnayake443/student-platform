<?php

namespace App\Filament\Resources\Grades\RelationManagers;

use App\Filament\Resources\LearningClasses\LearningClassResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;

class LearningClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'learningClasses';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Class Name')
                    ->required()
                    ->maxLength(255),


                Select::make('medium')
                    ->label('Medium')
                    ->options([
                        'Sinhala' => 'Sinhala',
                        'English' => 'English',
                        'Tamil' => 'Tamil',
                    ])
                    ->required(),


                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Class')
                    ->searchable(),

                Tables\Columns\TextColumn::make('medium')
                    ->label('Medium'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

            ])

            ->headerActions([
                CreateAction::make(),
            ])

            ->recordActions([

                ViewAction::make()
                    ->url(fn ($record) =>
                        LearningClassResource::getUrl('view', [
                            'record' => $record,
                        ])
                    ),

                EditAction::make(),

                DeleteAction::make(),

            ]);
    }
}