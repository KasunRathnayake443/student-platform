<?php

namespace App\Filament\SchoolAdmin\Resources\Students\Tables;

use App\Filament\Resources\Students\Tables\StudentsTable as BaseStudentsTable;
use App\Filament\SchoolAdmin\Scopes\SchoolAdminScopes;
use App\Filament\SchoolAdmin\Tables\Filters\SchoolPeopleFilters;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        $schoolIds = SchoolAdminScopes::schoolIds();

        return BaseStudentsTable::configure($table)
            ->pushFilters([
                SchoolPeopleFilters::studentSchool($schoolIds),
                SchoolPeopleFilters::studentGrade($schoolIds),
                SchoolPeopleFilters::studentClass($schoolIds),
                SchoolPeopleFilters::studentAcademicYear($schoolIds),
                SchoolPeopleFilters::studentEnrollmentStatus($schoolIds),
                SchoolPeopleFilters::studentGender(),
                SchoolPeopleFilters::passwordPending(),
            ]);
    }
}
