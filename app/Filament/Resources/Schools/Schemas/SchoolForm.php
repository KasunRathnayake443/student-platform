<?php

namespace App\Filament\Resources\Schools\Schemas;

use App\Models\School;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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

                Section::make('Email Delivery')
                    ->description('Configure the email account used to notify students in this school.')
                    ->schema([

                        TextInput::make('email')
                            ->label('Sender Email Address')
                            ->email()
                            ->required()
                            ->live()
                            ->maxLength(255)
                            ->helperText('This address is used as the SMTP username and sender.'),

                        TextInput::make('smtp_password')
                            ->label('SMTP Password or App Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->formatStateUsing(fn (): ?string => null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(function (?School $record, Get $get): bool {
                                if (! $record instanceof School || blank($record->smtp_password)) {
                                    return true;
                                }

                                return $get('email') !== $record->email
                                    || $get('smtp_host') !== $record->smtp_host;
                            })
                            ->maxLength(1024)
                            ->helperText('Stored encrypted. Leave blank to keep the current password. Re-enter it when the sender email or SMTP host changes.'),

                        TextInput::make('smtp_host')
                            ->label('SMTP Host')
                            ->required()
                            ->live()
                            ->maxLength(255)
                            ->placeholder('smtp.example.com'),

                        TextInput::make('smtp_port')
                            ->label('SMTP Port')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->default(587)
                            ->required(),

                        Select::make('smtp_encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS (usually port 587)',
                                'ssl' => 'SSL (usually port 465)',
                                'none' => 'None',
                            ])
                            ->default('tls')
                            ->required()
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active School')
                    ->default(true),

            ]);
    }
}
