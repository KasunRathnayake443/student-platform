<?php

namespace App\Filament\Resources\LearningClasses\Schemas;


use Filament\Schemas\Schema;

use Filament\Infolists\Components\TextEntry;



class LearningClassInfolist
{

    public static function configure(Schema $schema): Schema
    {

        return $schema

            ->components([


                TextEntry::make('name')

                    ->label('Class Name'),




                TextEntry::make('grade.school.name')

                    ->label('School'),




                TextEntry::make('grade.name')

                    ->label('Grade'),




                TextEntry::make('medium')

                    ->label('Medium'),




                TextEntry::make('is_active')

                    ->label('Status')

                    ->badge(),



            ]);

    }

}