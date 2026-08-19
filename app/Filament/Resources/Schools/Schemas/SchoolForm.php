<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                FileUpload::make('logo')
                    ->label('School Logo')
                    ->image()
                    ->directory('schools/logos')
                    ->imageEditor()
                    ->nullable(),

                TextInput::make('name')
                    ->label('School Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('School Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                Textarea::make('address')
                    ->label('School Address')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Active School')
                    ->default(true),

            ]);
    }
}
