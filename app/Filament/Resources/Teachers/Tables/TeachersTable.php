<?php

namespace App\Filament\Resources\Teachers\Tables;

use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class TeachersTable
{
    /**
     * @param  array<int, int>|null  $schoolIds
     */
    public static function configure(Table $table, ?Action $recordAction = null, ?array $schoolIds = null): Table
    {

        return $table

            ->columns([

                Tables\Columns\ImageColumn::make('profile_photo')

                    ->label('Photo')

                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')

                    ->label('Teacher Name')

                    ->searchable()

                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')

                    ->label('Email')

                    ->searchable(),

                Tables\Columns\TextColumn::make('employee_no')

                    ->label('Employee No'),

                Tables\Columns\TextColumn::make('phone')

                    ->label('Phone'),

                Tables\Columns\TextColumn::make('introduced_students_count')

                    ->label('Students Introduced')

                    ->getStateUsing(fn (Teacher $record): int => $record->introducedStudentsCount($schoolIds))

                    ->badge()

                    ->color('info'),

            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make(),

                $recordAction ?? TeacherResource::deleteFromSystemAction(),

            ]);

    }
}
