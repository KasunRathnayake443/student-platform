<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                TextInput::make('admission_no')
                    ->label('Admission Number')
                    ->required()
                    ->unique(ignoreRecord: true),


                DatePicker::make('date_of_birth')
                    ->label('Date of Birth'),


                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),


                TextInput::make('phone')
                    ->tel(),


                TextInput::make('parent_name')
                    ->label('Parent Name'),


                TextInput::make('parent_phone')
                    ->label('Parent Phone'),


            ]);
    }
}