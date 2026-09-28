<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                ImageEntry::make('logo')
                    ->label('School Logo')
                    ->circular()
                    ->size(120),

                TextEntry::make('name')
                    ->label('School Name')
                    ->weight('bold'),

                TextEntry::make('code')
                    ->label('School Code'),

                TextEntry::make('address')
                    ->label('Address')
                    ->columnSpanFull(),

                TextEntry::make('phone')
                    ->label('Phone Number'),

                TextEntry::make('email')
                    ->label('Email Address'),

                Section::make('Email Delivery')
                    ->schema([
                        TextEntry::make('smtp_host')
                            ->label('SMTP Host')
                            ->placeholder('Not configured'),

                        TextEntry::make('smtp_port')
                            ->label('SMTP Port'),

                        TextEntry::make('smtp_encryption')
                            ->label('Encryption')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                                'none' => 'None',
                                default => 'Not configured',
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                IconEntry::make('is_active')
                    ->label('Status')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),

            ]);
    }
}
