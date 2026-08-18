<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\LearningClass;
use App\Models\Teacher;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Quiz Information
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Information')
                    ->description('Basic information and instructions for this quiz.')
                    ->schema([

                        TextInput::make('title')
                            ->label('Quiz Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Short Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        RichEditor::make('instructions')
                            ->label('Quiz Instructions')
                            ->helperText('Explain the quiz rules and instructions shown to students before starting.')
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Class & Teacher
                |--------------------------------------------------------------------------
                */

                Section::make('Class & Teacher')
                    ->description('Select the class and the responsible teacher.')
                    ->schema([

                        Select::make('learning_class_id')
                            ->label('Learning Class')
                            ->options(
                                fn () => LearningClass::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('teacher_id', null))
                            ->required(),

                        Select::make('teacher_id')
                            ->label('Responsible Teacher')
                            ->options(function (Get $get) {
                                $classId = $get('learning_class_id');

                                $query = Teacher::query()->with('user');

                                if ($classId) {
                                    $query->whereHas('classes', function ($q) use ($classId) {
                                        $q->where('learning_classes.id', $classId);
                                    });
                                }

                                return $query->get()->mapWithKeys(function (Teacher $teacher) {
                                    return [
                                        $teacher->id => $teacher->user->name . ' - ' . $teacher->employee_no,
                                    ];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Teachers assigned to the selected learning class.'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Timing & Grading Rules
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Rules & Timing')
                    ->description('Configure time limits, attempt limits, and result display.')
                    ->schema([

                        TextInput::make('time_limit_minutes')
                            ->label('Time Limit')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->suffix('minutes')
                            ->helperText('Leave empty for unlimited time.'),

                        TextInput::make('max_attempts')
                            ->label('Max Attempts')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Leave empty for unlimited attempts.'),

                        TextInput::make('passing_percentage')
                            ->label('Passing Percentage')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(50)
                            ->suffix('%')
                            ->required(),

                        Toggle::make('show_correct_answers_after_submission')
                            ->label('Show Correct Answers')
                            ->default(true)
                            ->helperText('Reveal correct answers and explanations to students after submission.'),

                        Toggle::make('shuffle_questions')
                            ->label('Shuffle Questions')
                            ->default(false)
                            ->helperText('Randomize question order for each attempt.'),

                        Toggle::make('shuffle_options')
                            ->label('Shuffle Options')
                            ->default(false)
                            ->helperText('Randomize multiple-choice options for each question.'),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Availability
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Availability')
                    ->description('Control when students can access and attempt this quiz.')
                    ->schema([

                        Toggle::make('available_immediately')
                            ->label('Available Immediately')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(function (Set $set, bool $state): void {
                                $set('availability_type', $state ? 'immediate' : 'scheduled');
                                if ($state) {
                                    $set('start_at', null);
                                }
                            })
                            ->helperText('Turn this off if students should wait until a specific date and time.'),

                        TextInput::make('availability_type')
                            ->hidden()
                            ->dehydrated(true)
                            ->default('immediate'),

                        DateTimePicker::make('start_at')
                            ->label('Start Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required(fn (Get $get): bool => ! (bool) $get('available_immediately'))
                            ->hidden(fn (Get $get): bool => (bool) $get('available_immediately')),

                        DateTimePicker::make('end_at')
                            ->label('End Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->after('start_at')
                            ->helperText('Quiz attempts are closed after this date/time.'),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->helperText('Students will only see published quizzes.'),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | Questions & Options Builder
                |--------------------------------------------------------------------------
                */

                Section::make('Questions & Multiple-Choice Answers')
                    ->description('Add questions and provide 4-5 answer options with exactly one marked as correct.')
                    ->schema([

                        Repeater::make('questions')
                            ->label('Questions')
                            ->schema([

                                Textarea::make('question_text')
                                    ->label('Question')
                                    ->required()
                                    ->rows(2)
                                    ->columnSpanFull(),

                                TextInput::make('points')
                                    ->label('Points')
                                    ->numeric()
                                    ->integer()
                                    ->default(1)
                                    ->minValue(1)
                                    ->suffix('marks')
                                    ->required(),

                                Textarea::make('explanation')
                                    ->label('Explanation / Feedback Notes')
                                    ->rows(2)
                                    ->helperText('Optional explanation displayed when reviewing the quiz.')
                                    ->columnSpanFull(),

                                Repeater::make('options')
                                    ->label('Multiple Choice Options (Select the correct one)')
                                    ->schema([

                                        TextInput::make('option_text')
                                            ->label('Option Text')
                                            ->required(),

                                        Toggle::make('is_correct')
                                            ->label('Correct Answer')
                                            ->default(false)
                                            ->inline(false),

                                    ])
                                    ->columns(2)
                                    ->minItems(2)
                                    ->maxItems(6)
                                    ->defaultItems(4)
                                    ->collapsible()
                                    ->columnSpanFull(),

                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? 'New Question')
                            ->defaultItems(1)
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}
