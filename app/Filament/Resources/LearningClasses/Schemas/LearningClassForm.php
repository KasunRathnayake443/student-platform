<?php

namespace App\Filament\Resources\LearningClasses\Schemas;

use App\Filament\Rules\InScope;
use App\Models\Grade;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LearningClassForm
{
    /**
     * @param  array{schoolIds?: array<int, int>|null}  $options
     */
    public static function configure(Schema $schema, array $options = []): Schema
    {
        $schoolIds = $options['schoolIds'] ?? null;

        $gradeIds = $schoolIds === null
            ? null
            : Grade::query()
                ->whereIn('school_id', $schoolIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

        return $schema

            ->components([

                TextInput::make('name')

                    ->label('Class Name')

                    ->required()

                    ->maxLength(255),

                Select::make('grade_id')

                    ->label('Grade')

                    ->options(function () use ($schoolIds) {

                        return Grade::query()

                            ->when($schoolIds, fn ($query) => $query->whereIn('school_id', $schoolIds))

                            ->with('school')

                            ->orderBy('school_id')

                            ->orderBy('name')

                            ->get()

                            ->mapWithKeys(function ($grade) {

                                return [

                                    $grade->id => $grade->school->name
                                    .' → Grade '
                                    .$grade->name,

                                ];

                            });

                    })

                    ->searchable()

                    ->preload()

                    ->rules([
                        new InScope($gradeIds, 'The selected grade is outside your assigned schools.'),
                    ])

                    ->required(),

                Select::make('medium')

                    ->label('Medium')

                    ->options([

                        'Sinhala' => 'Sinhala',

                        'English' => 'English',

                        'Tamil' => 'Tamil',

                    ])

                    ->required(),

                Toggle::make('is_active')

                    ->label('Active')

                    ->default(true),

            ]);

    }
}
