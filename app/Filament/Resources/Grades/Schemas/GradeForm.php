<?php

namespace App\Filament\Resources\Grades\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\School;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Grade Name')
                    ->required()
                    ->maxLength(255),


                Select::make('school_id')
                    ->label('School')
                    ->relationship(
                        'school',
                        'name'
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

            ]);
    }
}