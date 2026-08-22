<?php

namespace App\Filament\Teacher\Resources\LearningClasses\RelationManagers;

use App\Filament\Teacher\Resources\Teachers\TeacherResource;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    protected static ?string $title = 'Teachers';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user'))
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee_no')
                    ->label('Employee No'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone'),
            ])
            ->recordActions([
                Action::make('viewTeacher')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn (Teacher $record) => TeacherResource::getUrl('view', ['record' => $record], panel: 'teacher')
                    ),
            ]);
    }
}
