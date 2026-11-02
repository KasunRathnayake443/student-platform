<?php

namespace App\Filament\SchoolAdmin\Resources\Quizzes\Tables;

use App\Filament\Resources\Quizzes\Tables\QuizzesTable as BaseQuizzesTable;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Filament\SchoolAdmin\Tables\Filters\TeachingContentFilters;
use Filament\Tables\Table;

class QuizzesTable
{
    public static function configure(Table $table): Table
    {
        $schoolIds = SchoolAdminScopes::schoolIds();

        return BaseQuizzesTable::configure($table)
            ->pushFilters([
                ...TeachingContentFilters::common($schoolIds),
                TeachingContentFilters::availabilityStatus(),
                TeachingContentFilters::hasTimeLimit(),
                TeachingContentFilters::hasAttempts(),
            ]);
    }
}
