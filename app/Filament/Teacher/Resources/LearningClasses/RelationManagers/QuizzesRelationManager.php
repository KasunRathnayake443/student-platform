<?php

namespace App\Filament\Teacher\Resources\LearningClasses\RelationManagers;

use App\Filament\Teacher\Resources\Quizzes\QuizResource;
use App\Models\LearningClass;
use App\Models\Quiz;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizzes';

    protected static ?string $title = 'Quizzes';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['teacher.user', 'teachers.user'])
                ->withCount(['questions', 'attempts']))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Quiz Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignee_names')
                    ->label('Teachers')
                    ->badge()
                    ->state(function (Quiz $record): array {
                        $names = $record->teachers
                            ->map(fn (Teacher $teacher) => $teacher->user->name);

                        if ($names->isEmpty() && $record->teacher) {
                            return [$record->teacher->user->name];
                        }

                        return $names->values()->toArray();
                    }),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Questions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_points')
                    ->label('Total Points')
                    ->suffix(' pts')
                    ->sortable(),

                Tables\Columns\TextColumn::make('time_limit_minutes')
                    ->label('Time Limit')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}m" : 'Untimed')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('createQuiz')
                    ->label('Create Quiz')
                    ->icon('heroicon-o-plus')
                    ->form([

                        Section::make('Quiz Information')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Quiz Title')
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('description')
                                    ->label('Short Description')
                                    ->rows(2)
                                    ->maxLength(1000)
                                    ->columnSpanFull(),

                                RichEditor::make('instructions')
                                    ->label('Quiz Instructions')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Assigned Teachers')
                            ->description(
                                'You are assigned by default. Co-teachers of this class can be added to manage this quiz together.'
                            )
                            ->schema([
                                Select::make('teacher_ids')
                                    ->label('Teachers')
                                    ->options(function () {
                                        $class = $this->getOwnerRecord();

                                        if (! $class instanceof LearningClass) {
                                            return [];
                                        }

                                        return $class
                                            ->teachers()
                                            ->with('user')
                                            ->get()
                                            ->mapWithKeys(
                                                fn (Teacher $teacher) => [
                                                    $teacher->id => $teacher->user->name
                                                        .' - '
                                                        .$teacher->employee_no,
                                                ]
                                            );
                                    })
                                    ->default(auth()->user()->teacher?->id)
                                    ->multiple()
                                    ->minItems(1)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Quiz Rules & Timing')
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
                                    ->helperText('Leave empty or 0 for unlimited attempts.'),

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
                                    ->label('Show Answers After Submission')
                                    ->default(true),

                                Toggle::make('shuffle_questions')
                                    ->label('Shuffle Questions')
                                    ->default(false),

                                Toggle::make('shuffle_options')
                                    ->label('Shuffle Options')
                                    ->default(false),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),

                        Section::make('Availability')
                            ->schema([
                                Toggle::make('available_immediately')
                                    ->label('Available Immediately')
                                    ->default(true)
                                    ->live(),

                                DateTimePicker::make('start_at')
                                    ->label('Start Date & Time')
                                    ->seconds(false)
                                    ->native(false)
                                    ->required(
                                        fn (Get $get): bool => ! (bool) $get('available_immediately')
                                    )
                                    ->hidden(
                                        fn (Get $get): bool => (bool) $get('available_immediately')
                                    ),

                                DateTimePicker::make('end_at')
                                    ->label('End Date & Time')
                                    ->seconds(false)
                                    ->native(false)
                                    ->required()
                                    ->after('start_at'),

                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Questions & Answers')
                            ->description('Add questions with multiple-choice options.')
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
                                            ->suffix('pts')
                                            ->required(),

                                        Textarea::make('explanation')
                                            ->label('Explanation / Feedback')
                                            ->rows(2)
                                            ->columnSpanFull(),

                                        Repeater::make('options')
                                            ->label('Multiple Choice Options (Check the correct answer)')
                                            ->schema([
                                                TextInput::make('option_text')
                                                    ->label('Option')
                                                    ->required(),

                                                Toggle::make('is_correct')
                                                    ->label('Correct')
                                                    ->default(false),
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
                                    ->defaultItems(1)
                                    ->itemLabel(fn (array $state): string => $state['question_text'] ?? 'New Question')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        $class = $this->getOwnerRecord();

                        if (! $class instanceof LearningClass) {
                            return;
                        }

                        $quiz = Quiz::create([
                            'learning_class_id' => $class->getKey(),
                            'teacher_id' => auth()->user()->teacher?->id,

                            'title' => $data['title'],
                            'description' => $data['description'] ?? null,
                            'instructions' => $data['instructions'] ?? null,

                            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                            'max_attempts' => $data['max_attempts'] ?? 1,
                            'passing_percentage' => $data['passing_percentage'] ?? 50,
                            'total_points' => 0,

                            'show_correct_answers_after_submission' => $data['show_correct_answers_after_submission'] ?? true,
                            'shuffle_questions' => $data['shuffle_questions'] ?? false,
                            'shuffle_options' => $data['shuffle_options'] ?? false,

                            'availability_type' => ($data['available_immediately'] ?? true)
                                    ? 'immediate'
                                    : 'scheduled',
                            'start_at' => ($data['available_immediately'] ?? true)
                                ? null
                                : ($data['start_at'] ?? null),
                            'end_at' => $data['end_at'] ?? null,

                            'is_published' => $data['is_published'] ?? true,
                        ]);

                        /*
                         * Every selected teacher becomes an assignee so all
                         * of them can manage and review the quiz.
                         */
                        $quiz->teachers()->sync(
                            array_map(intval(...), $data['teacher_ids'])
                        );

                        $questionsData = $data['questions'] ?? [];
                        $totalPoints = 0;

                        foreach ($questionsData as $index => $qData) {
                            $points = (int) ($qData['points'] ?? 1);
                            $totalPoints += $points;

                            $question = $quiz->questions()->create([
                                'question_text' => $qData['question_text'],
                                'points' => $points,
                                'explanation' => $qData['explanation'] ?? null,
                                'sort_order' => $index + 1,
                            ]);

                            $optionsData = $qData['options'] ?? [];
                            foreach ($optionsData as $optIndex => $optData) {
                                $question->options()->create([
                                    'option_text' => $optData['option_text'],
                                    'is_correct' => (bool) ($optData['is_correct'] ?? false),
                                    'sort_order' => $optIndex + 1,
                                ]);
                            }
                        }

                        if ($totalPoints > 0) {
                            $quiz->updateQuietly(['total_points' => $totalPoints]);
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(
                        fn (Quiz $record) => QuizResource::getUrl('view', ['record' => $record], panel: 'teacher')
                    ),

                EditAction::make()
                    ->url(
                        fn (Quiz $record) => QuizResource::getUrl('edit', ['record' => $record], panel: 'teacher')
                    ),
            ]);
    }
}
