<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeacherInfolist
{
    public static function configure(Schema $schema): Schema
    {

        return $schema

            ->components([

                ImageEntry::make('profile_photo')

                    ->label('Profile Picture')

                    ->circular(),

                TextEntry::make('user.name')

                    ->label('Teacher Name')

                    ->weight('bold'),

                TextEntry::make('user.email')

                    ->label('Email'),

                TextEntry::make('employee_no')

                    ->label('Employee Number'),

                TextEntry::make('phone')

                    ->label('Phone'),

                TextEntry::make('address')

                    ->label('Address')

                    ->columnSpanFull(),

                TextEntry::make('schools.name')

                    ->label('Assigned Schools')

                    ->listWithLineBreaks()

                    ->bulleted(),

            ]);

    }
}
