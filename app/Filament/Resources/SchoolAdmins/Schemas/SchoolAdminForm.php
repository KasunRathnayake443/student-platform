<?php

namespace App\Filament\Resources\SchoolAdmins\Schemas;


use Filament\Schemas\Schema;


use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;



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

                    ->required(fn ($record)=>!$record),




                TextInput::make('phone'),




                Textarea::make('address'),





                Select::make('schools')

                    ->label('Assigned Schools')

                    ->multiple()

                    ->options(
                        \App\Models\School::pluck(
                            'name',
                            'id'
                        )
                    )

                    ->searchable()

                    ->preload(),



            ]);


    }


}