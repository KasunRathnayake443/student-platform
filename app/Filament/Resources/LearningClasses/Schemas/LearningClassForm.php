<?php

namespace App\Filament\Resources\LearningClasses\Schemas;


use App\Models\Grade;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Schemas\Schema;



class LearningClassForm
{

    public static function configure(Schema $schema): Schema
    {

        return $schema

            ->components([



                TextInput::make('name')

                    ->label('Class Name')

                    ->required()

                    ->maxLength(255),





                Select::make('grade_id')

                    ->label('Grade')

                    ->options(function(){

                        return Grade::with('school')

                            ->get()

                            ->mapWithKeys(function($grade){

                                return [

                                    $grade->id =>

                                    $grade->school->name
                                    .' → Grade '
                                    .$grade->name

                                ];

                            });

                    })

                    ->searchable()

                    ->preload()

                    ->required(),






                Select::make('medium')

                    ->label('Medium')

                    ->options([

                        'Sinhala'=>'Sinhala',

                        'English'=>'English',

                        'Tamil'=>'Tamil',

                    ])

                    ->required(),





                Toggle::make('is_active')

                    ->label('Active')

                    ->default(true),


            ]);

    }

}