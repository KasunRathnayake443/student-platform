<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Schools\SchoolResource;
use App\Models\School;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestSchoolsTableWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Registered Schools Overview')
            ->description('Active educational institutions operating on the platform')
            ->query(School::query()->latest())
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-school.png')),

                TextColumn::make('name')
                    ->label('School Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('grades_count')
                    ->counts('grades')
                    ->label('Grades')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('teachers_count')
                    ->counts('teachers')
                    ->label('Teachers')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (School $record): string => SchoolResource::getUrl('view', ['record' => $record])),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (School $record): string => SchoolResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10, 20])
            ->defaultPaginationPageOption(5);
    }
}
