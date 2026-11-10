<?php

namespace App\Filament\Resources\LearningClasses\Schemas;

use App\Filament\Rules\InScope;
use App\Models\Grade;
use App\Models\LearningClass;
use App\Models\School;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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

                Select::make('school_id')

                    ->label('School')

                    ->options(function () use ($schoolIds) {

                        return School::query()

                            ->when($schoolIds !== null, fn ($query) => $query->whereIn('id', $schoolIds))

                            ->orderBy('name')

                            ->pluck('name', 'id');

                    })

                    ->searchable()

                    ->preload()

                    ->live()

                    ->dehydrated(false)

                    ->default(function (?LearningClass $record) use ($schoolIds) {

                        if ($record?->grade?->school_id) {

                            return $record->grade->school_id;

                        }

                        $requested = request()->get('school_id');

                        if (
                            $requested
                            && ($schoolIds === null || in_array((int) $requested, $schoolIds, true))
                        ) {

                            return (int) $requested;

                        }

                        $requestedGrade = request()->get('grade_id');

                        if ($requestedGrade) {

                            $grade = Grade::find($requestedGrade);

                            if (
                                $grade
                                && ($schoolIds === null || in_array((int) $grade->school_id, $schoolIds, true))
                            ) {

                                return (int) $grade->school_id;

                            }

                        }

                        if ($schoolIds !== null && count($schoolIds) === 1) {

                            return $schoolIds[0];

                        }

                        return null;

                    })

                    ->afterStateHydrated(function (?LearningClass $record, mixed $state, Set $set): void {

                        if (filled($state)) {

                            return;

                        }

                        if ($record?->grade?->school_id) {

                            $set('school_id', (int) $record->grade->school_id);

                        }

                    })

                    ->afterStateUpdated(fn (Set $set) => $set('grade_id', null))

                    ->rules([
                        new InScope($schoolIds, 'The selected school is outside your assigned schools.'),
                    ])

                    ->required(),

                TextInput::make('name')

                    ->label('Class Name')

                    ->required()

                    ->maxLength(255),

                Select::make('grade_id')

                    ->label('Grade')

                    ->options(function (Get $get) use ($schoolIds) {

                        $schoolId = $get('school_id');

                        if (blank($schoolId)) {

                            return [];

                        }

                        return Grade::query()

                            ->where('school_id', $schoolId)

                            ->when($schoolIds !== null, fn ($query) => $query->whereIn('school_id', $schoolIds))

                            ->orderBy('name')

                            ->pluck('name', 'id');

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

                Select::make('emoji')

                    ->label('Class Emoji')

                    ->options(LearningClass::emojiOptions())

                    ->searchable()

                    ->default(LearningClass::DEFAULT_EMOJI)

                    ->helperText('This emoji is shown on the class card in the kids dashboard. Pick one that matches the subject.'),

                Toggle::make('is_active')

                    ->label('Active')

                    ->default(true),

            ]);

    }
}
