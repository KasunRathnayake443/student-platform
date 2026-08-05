<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Schemas\Schema;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;


class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                ImageEntry::make('profile_photo')
                    ->label('Profile Picture')
                    ->circular(),



                TextEntry::make('user.name')
                    ->label('Student Name')
                    ->weight('bold'),



                TextEntry::make('user.email')
                    ->label('Email'),



                TextEntry::make('admission_no')
                    ->label('Admission Number'),



                TextEntry::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date(),



                TextEntry::make('gender')
                    ->label('Gender'),



                TextEntry::make('phone')
                    ->label('Phone'),



                TextEntry::make('address')
                    ->label('Address')
                    ->columnSpanFull(),




                TextEntry::make('parent_name')
                    ->label('Parent Name'),



                TextEntry::make('parent_phone')
                    ->label('Parent Phone'),




                TextEntry::make('currentEnrollment.school.name')
                    ->label('School'),



                TextEntry::make('currentEnrollment.grade.name')
                    ->label('Grade'),



                TextEntry::make('currentEnrollment.academic_year')
                    ->label('Academic Year'),



                TextEntry::make('currentEnrollment.status')
                    ->label('Enrollment Status'),


            ]);
    }
}