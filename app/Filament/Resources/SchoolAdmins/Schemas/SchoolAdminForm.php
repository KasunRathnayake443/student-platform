<?php

namespace App\Filament\Resources\SchoolAdmins\Schemas;


use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use App\Models\School;



class SchoolAdminForm
{


    public static function configure(Schema $schema): Schema
    {


        return $schema

            ->components([



                TextInput::make('name')

                    ->required(),




                TextInput::make('email')

                    ->email()

                    ->required(),





                TextInput::make('password')

                    ->password()

                    ->required(fn($record)=> !$record),





                Select::make('schools')


                    ->label('Assigned Schools')


                    ->multiple()


                    ->relationship(
                        'schools',
                        'name'
                    )


                    ->searchable()

                    ->preload(),



            ]);

    }


}