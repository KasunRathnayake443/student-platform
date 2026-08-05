<?php

namespace App\Filament\Resources\SchoolAdmins\Schemas;


use Filament\Schemas\Schema;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;



class SchoolAdminInfolist
{


    public static function configure(Schema $schema): Schema
    {


        return $schema

            ->components([



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