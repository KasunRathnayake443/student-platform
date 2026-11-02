<?php

namespace App\Filament\SchoolAdmin\Resources\Lessons\Tables;

use App\Filament\Resources\Lessons\Tables\LessonsTable as BaseLessonsTable;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Filament\SchoolAdmin\Tables\Filters\TeachingContentFilters;
use Filament\Tables\Table;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        $schoolIds = SchoolAdminScopes::schoolIds();

        return BaseLessonsTable::configure($table)
            ->pushFilters([
                ...TeachingContentFilters::common($schoolIds),
                TeachingContentFilters::hasAttachments(),
                TeachingContentFilters::hasVideo(),
            ]);
    }
}
