<?php

namespace App\Filament\Resources\Schools\RelationManagers;

use App\Filament\Resources\Grades\GradeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GradesRelationManager extends RelationManager
{
    protected static string $relationship = 'grades';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Grade Name')
                    ->required(),

                Toggle::make('is_active')
                    ->default(true),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Grade')
                    ->searchable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students'),

                Tables\Columns\TextColumn::make('learning_classes_count')
                    ->counts('learningClasses')
                    ->label('Classes'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

            ])

            ->headerActions([

                Action::make('addGrade')

                    ->label('Add New Grade')

                    ->icon('heroicon-o-plus')

                    ->url(fn () => GradeResource::getUrl(
                        'create',
                        [
                            'school_id' => $this->getOwnerRecord()->id,
                        ]
                    )
                    ),

            ])
            ->recordActions([

                ViewAction::make()
                    ->url(fn ($record) => GradeResource::getUrl('view', [
                        'record' => $record,
                    ])
                    ),

                EditAction::make(),

                DeleteAction::make(),

            ]);
    }
}
