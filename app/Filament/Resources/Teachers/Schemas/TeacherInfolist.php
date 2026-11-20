<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Models\Teacher;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeacherInfolist
{
    /**
     * @param  array<int, int>|null  $schoolIds
     */
    public static function configure(Schema $schema, ?array $schoolIds = null): Schema
    {

        return $schema

            ->components([

                ImageEntry::make('profile_photo')

                    ->label('Profile Picture')

                    ->circular(),

                TextEntry::make('user.name')

                    ->label('Teacher Name')

                    ->weight('bold'),

                TextEntry::make('user.email')

                    ->label('Email'),

                TextEntry::make('employee_no')

                    ->label('Employee Number'),

                TextEntry::make('phone')

                    ->label('Phone'),

                TextEntry::make('address')

                    ->label('Address')

                    ->columnSpanFull(),

                TextEntry::make('schools.name')

                    ->label('Assigned Schools')

                    ->listWithLineBreaks()

                    ->bulleted(),

                TextEntry::make('introduced_students_count')

                    ->label('Students Introduced')

                    ->getStateUsing(fn (Teacher $record): int => $record->introducedStudentsCount($schoolIds))

                    ->badge()

                    ->color('info'),

            ]);

    }
}
