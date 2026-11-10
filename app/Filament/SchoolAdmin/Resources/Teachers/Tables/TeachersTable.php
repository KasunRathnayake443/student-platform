<?php

namespace App\Filament\SchoolAdmin\Resources\Teachers\Tables;

use App\Filament\Resources\Teachers\Tables\TeachersTable as BaseTeachersTable;
use App\Filament\SchoolAdmin\Resources\Teachers\TeacherResource;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Filament\SchoolAdmin\Tables\Filters\SchoolPeopleFilters;
use Filament\Tables\Table;

class TeachersTable
{
    public static function configure(Table $table): Table
    {
        $schoolIds = SchoolAdminScopes::schoolIds();

        return BaseTeachersTable::configure($table, TeacherResource::removeFromSchoolAction())
            ->pushFilters([
                SchoolPeopleFilters::teacherSchool($schoolIds),
                SchoolPeopleFilters::teacherClass($schoolIds),
                SchoolPeopleFilters::teacherHasClasses(),
                SchoolPeopleFilters::passwordPending(),
            ]);
    }
}
