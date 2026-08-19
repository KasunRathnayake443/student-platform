<?php

namespace App\Filament\Resources\LearningClasses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningClassInfolist
{
    public static function configure(Schema $schema): Schema
    {

        return $schema

            ->components([

                Section::make('Class Information')

                    ->schema([

                        Grid::make(2)

                            ->schema([

                                TextEntry::make('name')

                                    ->label('Class Name'),

                                TextEntry::make('medium')

                                    ->label('Medium'),

                                TextEntry::make('grade.school.name')

                                    ->label('School'),

                                TextEntry::make('grade.name')

                                    ->label('Grade'),

                                TextEntry::make('students_count')

                                    ->label('Students')

                                    ->badge(),

                                TextEntry::make('teachers_count')

                                    ->label('Teachers')

                                    ->badge(),

                                TextEntry::make('is_active')

                                    ->label('Status')

                                    ->badge(),

                            ]),

                    ]),

            ]);

    }
}
