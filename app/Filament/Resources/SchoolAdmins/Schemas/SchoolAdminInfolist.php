<?php

namespace App\Filament\Resources\SchoolAdmins\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SchoolAdminInfolist
{
    public static function configure(Schema $schema): Schema
    {

        return $schema

            ->components([

                ImageEntry::make('profile_photo')
                    ->label('Profile Picture')
                    ->circular(),

                TextEntry::make('user.name')

                    ->label('Name')

                    ->weight('bold'),

                TextEntry::make('user.email')

                    ->label('Email'),

                TextEntry::make('phone')

                    ->label('Phone'),

                TextEntry::make('address')

                    ->label('Address')

                    ->columnSpanFull(),

                TextEntry::make('schools.name')

                    ->label('Assigned Schools')

                    ->listWithLineBreaks(),

                IconEntry::make('user.must_change_password')

                    ->label('Password Change Required')

                    ->boolean(),

                TextEntry::make('created_at')

                    ->label('Created')

                    ->dateTime(),

            ]);

    }
}
