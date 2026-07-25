<?php

namespace App\Filament\Resources\Schools\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\School;

class SchoolStats extends StatsOverviewWidget
{

    public ?School $record = null;


    protected function getStats(): array
    {

        $school = $this->record;


        if (! $school) {
            return [];
        }


        return [

            Stat::make(
                'Students',
                $school->students()->count()
            )
            ->description('Total Students')
            ->icon('heroicon-o-academic-cap'),


            Stat::make(
                'Grades',
                $school->grades()->count()
            )
            ->description('Total Grades')
            ->icon('heroicon-o-book-open'),


            Stat::make(
                'Classes',
                $school->classes()->count()
            )
            ->description('Learning Classes')
            ->icon('heroicon-o-building-library'),

        ];
    }
}