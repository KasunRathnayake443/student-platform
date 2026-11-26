<?php

namespace App\Filament\Resources\ContactSubmissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact Message')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('email')
                            ->label('Email'),

                        TextEntry::make('phone')
                            ->label('Phone')
                            ->placeholder('Not provided'),

                        TextEntry::make('created_at')
                            ->label('Submitted')
                            ->dateTime(),

                        TextEntry::make('message')
                            ->label('Message')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
