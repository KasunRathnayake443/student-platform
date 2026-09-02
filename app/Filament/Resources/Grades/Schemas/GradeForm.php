<?php

namespace App\Filament\Resources\Grades\Schemas;

use App\Filament\Rules\InScope;
use App\Models\School;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GradeForm
{
    /**
     * @param  array{schoolIds?: array<int, int>|null}  $options
     */
    public static function configure(Schema $schema, array $options = []): Schema
    {
        $schoolIds = $options['schoolIds'] ?? null;

        return $schema
            ->components([

                Select::make('school_id')
                    ->label('School')
                    ->options(function () use ($schoolIds) {
                        return School::query()
                            ->when($schoolIds, fn ($query) => $query->whereIn('id', $schoolIds))
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->rules([
                        new InScope($schoolIds, 'The selected school is outside your assigned schools.'),
                    ])
                    ->required()
                    ->default(function () use ($schoolIds) {
                        $requested = request()->get('school_id');

                        if ($requested && ($schoolIds === null || in_array((int) $requested, $schoolIds, true))) {
                            return $requested;
                        }

                        return null;
                    })
                    ->disabled(fn () => request()->has('school_id')),

                TextInput::make('name')
                    ->label('Grade Name')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

            ]);
    }
}
