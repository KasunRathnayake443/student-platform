<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\LearningClass;
use App\Models\Teacher;
use App\Services\QuizImportService;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class QuizForm
{
    /**
     * Options:
     *  - 'teacher' (bool): when true the current teacher is pre-assigned and
     *    the class selector is limited to the classes the teacher teaches.
     *  - 'learningClassId' (int|null): a learning class to pre-select.
     *
     * @param  array{teacher?: bool, learningClassId?: int|null}  $options
     */
    public static function configure(Schema $schema, array $options = []): Schema
    {
        $isTeacher = (bool) ($options['teacher'] ?? false);
        $contextClassId = $options['learningClassId'] ?? null;

        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | 1. Quiz Information
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Information')
                    ->description('Set the title, overview, and instructions displayed to students.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([

                        TextInput::make('title')
                            ->label('Quiz Title')
                            ->placeholder('e.g. Unit 4: Thermodynamics & Newton’s Laws')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Short Description')
                            ->placeholder('Brief overview of what this quiz covers...')
                            ->rows(2)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        RichEditor::make('instructions')
                            ->label('Quiz Instructions & Guidelines')
                            ->helperText('Clear instructions and rules shown to students before they begin their attempt.')
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 2. Class & Teachers
                |--------------------------------------------------------------------------
                */

                Section::make('Class & Assigned Teachers')
                    ->description('Select the target learning class and responsible teachers.')
                    ->icon('heroicon-o-user-group')
                    ->schema([

                        Select::make('learning_class_id')
                            ->label('Learning Class')
                            ->options(function () use ($isTeacher): array {
                                $query = LearningClass::query()->orderBy('name');

                                if ($isTeacher && auth()->user()?->teacher) {
                                    $teacher = auth()->user()->teacher;

                                    $query->whereHas('teachers', function ($q) use ($teacher): void {
                                        $q->where('teachers.id', $teacher->getKey());
                                    });
                                }

                                return $query->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(function () use ($isTeacher, $contextClassId) {
                                if ($contextClassId) {
                                    return $contextClassId;
                                }

                                if ($isTeacher && auth()->user()?->teacher) {
                                    $teacherClasses = auth()->user()->teacher->classes()->pluck('learning_classes.id');
                                    if ($teacherClasses->count() === 1) {
                                        return $teacherClasses->first();
                                    }
                                }

                                return null;
                            })
                            ->afterStateUpdated(function (Set $set, mixed $state) use ($isTeacher): void {
                                if ($isTeacher && auth()->user()?->teacher) {
                                    $set('teacher_ids', [auth()->user()->teacher->getKey()]);
                                } else {
                                    $set('teacher_ids', []);
                                }
                            })
                            ->required(),

                        Select::make('teacher_ids')
                            ->label('Assigned Teachers')
                            ->options(function (Get $get) use ($isTeacher): array {
                                $classId = $get('learning_class_id');

                                if (! $classId) {
                                    if ($isTeacher && auth()->user()?->teacher) {
                                        $t = auth()->user()->teacher;

                                        return [$t->id => $t->user->name.' - '.$t->employee_no];
                                    }

                                    return [];
                                }

                                return Teacher::query()
                                    ->whereHas('classes', function ($q) use ($classId): void {
                                        $q->where('learning_classes.id', $classId);
                                    })
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(fn (Teacher $teacher) => [
                                        $teacher->id => $teacher->user->name.' - '.$teacher->employee_no,
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->minItems(1)
                            ->required()
                            ->default(
                                $isTeacher && auth()->user()?->teacher
                                    ? [auth()->user()->teacher->getKey()]
                                    : []
                            )
                            ->rules([
                                fn (Get $get) => static function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $classId = $get('learning_class_id');

                                    if (! $classId || ! is_array($value) || $value === []) {
                                        return;
                                    }

                                    $teacherIds = array_values(
                                        array_unique(array_map(intval(...), $value))
                                    );

                                    $belongingCount = Teacher::query()
                                        ->whereIn('teachers.id', $teacherIds)
                                        ->whereHas('classes', function ($q) use ($classId): void {
                                            $q->where('learning_classes.id', $classId);
                                        })
                                        ->count();

                                    if ($belongingCount !== count($teacherIds)) {
                                        $fail('Only teachers assigned to the selected learning class can manage this quiz.');
                                    }
                                },
                            ])
                            ->helperText(
                                $isTeacher
                                    ? 'You are assigned as the responsible teacher by default.'
                                    : 'All selected teachers can manage this quiz and review student attempts.'
                            )
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 3. Rules & Timing
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Rules & Scoring')
                    ->description('Set time limits, allowed attempts, passing criteria, and feedback display.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([

                        TextInput::make('time_limit_minutes')
                            ->label('Time Limit (Minutes)')
                            ->placeholder('e.g. 30 (Leave empty for untimed)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->suffix('min')
                            ->helperText('Leave blank if students have unlimited time to complete the quiz.'),

                        TextInput::make('max_attempts')
                            ->label('Max Attempts')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->helperText('Maximum number of attempts allowed per student (default is 1).'),

                        TextInput::make('passing_percentage')
                            ->label('Passing Percentage')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(50)
                            ->suffix('%')
                            ->required()
                            ->helperText('Minimum score percentage required to pass this quiz.'),

                        Toggle::make('show_correct_answers_after_submission')
                            ->label('Show Correct Answers')
                            ->default(true)
                            ->helperText('Reveal correct answers, question explanations, and feedback after submission.'),

                        Toggle::make('shuffle_questions')
                            ->label('Shuffle Questions')
                            ->default(false)
                            ->helperText('Randomize question order for every student attempt.'),

                        Toggle::make('shuffle_options')
                            ->label('Shuffle Choices')
                            ->default(false)
                            ->helperText('Randomize multiple-choice option order for each question.'),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 4. Availability & Publishing
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Availability')
                    ->description('Control when students can access and attempt this quiz.')
                    ->icon('heroicon-o-clock')
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
                            ->helperText('Enable to allow students to take this quiz immediately once published.'),

                        Toggle::make('is_published')
                            ->label('Published Status')
                            ->default(true)
                            ->helperText('Students can only see and attempt published quizzes.'),

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
                            ->label('End Date & Time (Deadline)')
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->after('start_at')
                            ->helperText('Students can no longer start new attempts after this date and time.')
                            ->columnSpan(fn (Get $get): int => (bool) $get('available_immediately') ? 2 : 1),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 5. Quick CSV Import
                |--------------------------------------------------------------------------
                */

                Section::make('Quick Import Questions from CSV')
                    ->description('Save time by uploading a CSV file. Questions will automatically load into the editor below for review and editing.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([

                        Placeholder::make('import_hint')
                            ->label('CSV Format Guide')
                            ->content(
                                fn () => new HtmlString(
                                    '<div class="text-sm text-gray-600 dark:text-gray-400">'
                                    .'Required headers: <code>question</code>, <code>option_a</code>, <code>option_b</code>, <code>correct_option</code> (A-F), <code>points</code>, <code>explanation</code>.<br>'
                                    .'Supports up to 6 choices (<code>option_a</code> to <code>option_f</code>). '
                                    .'<a class="text-primary-600 dark:text-primary-400 font-medium underline" href="'.e(route('quiz-questions.import.template')).'" target="_blank">📥 Download Sample CSV Template</a>'
                                    .'</div>'
                                )
                            )
                            ->columnSpanFull(),

                        FileUpload::make('import_file')
                            ->label('Drop or Select Questions CSV File')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'text/comma-separated-values',
                                'application/csv',
                                'application/vnd.ms-excel',
                            ])
                            ->disk((string) config('filament.default_filesystem_disk', 'local'))
                            ->directory('quiz_imports')
                            ->maxSize(5120)
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                                if (blank($state)) {
                                    return;
                                }

                                try {
                                    $service = app(QuizImportService::class);
                                    $file = is_array($state) ? ($state[0] ?? null) : $state;
                                    $imported = [];

                                    if ($file instanceof UploadedFile || $file instanceof TemporaryUploadedFile) {
                                        $imported = $service->parse($file);
                                    } elseif (is_string($file)) {
                                        $imported = $service->parseStoredPath($file);
                                    }

                                    if (! empty($imported)) {
                                        $existing = $get('questions') ?? [];
                                        $nonEmpty = array_values(array_filter($existing, function ($q): bool {
                                            return filled(trim($q['question_text'] ?? ''));
                                        }));

                                        $merged = array_merge($nonEmpty, $imported);
                                        $set('questions', $merged);

                                        Notification::make()
                                            ->title('Loaded '.count($imported).' question(s) from CSV')
                                            ->body('All questions have been populated below. You can review, edit text, attach images or videos, and adjust choices before saving.')
                                            ->success()
                                            ->send();
                                    }
                                } catch (Throwable $e) {
                                    Notification::make()
                                        ->title('Could not import CSV questions')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->helperText('Upload a CSV file and questions will automatically load into the Questions & Multiple-Choice Answers section below.')
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 6. Questions & Options Builder
                |--------------------------------------------------------------------------
                */

                Section::make('Questions & Multiple-Choice Answers')
                    ->description('Build questions, attach optional images or videos, and configure multiple-choice answer options.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([

                        Repeater::make('questions')
                            ->label('')
                            ->schema([

                                TextInput::make('question_text')
                                    ->label('Question Prompt')
                                    ->placeholder('Enter the question prompt or problem statement...')
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('points')
                                    ->label('Points / Marks')
                                    ->numeric()
                                    ->integer()
                                    ->default(1)
                                    ->minValue(1)
                                    ->suffix('marks')
                                    ->required()
                                    ->columnSpan(1),

                                FileUpload::make('question_image')
                                    ->label('Question Image (Optional)')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                    ->image()
                                    ->disk((string) config('filament.default_filesystem_disk', 'local'))
                                    ->directory('quiz_questions/images')
                                    ->maxSize(5120)
                                    ->helperText('Attach an illustration or diagram (JPEG, PNG, WebP - max 5 MB).')
                                    ->columnSpan(2),

                                FileUpload::make('question_video')
                                    ->label('Question Video (Optional)')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/*'])
                                    ->disk((string) config('filament.default_filesystem_disk', 'local'))
                                    ->directory('quiz_questions/videos')
                                    ->maxSize(20480)
                                    ->helperText('Attach a video clip (MP4, WebM, MOV - max 20 MB).')
                                    ->columnSpan(2),

                                Textarea::make('explanation')
                                    ->label('Explanation / Solution Feedback (Optional)')
                                    ->placeholder('Provide step-by-step reasoning or feedback displayed when reviewing answers...')
                                    ->rows(2)
                                    ->helperText('Shown to students after completing the quiz when "Show Correct Answers" is enabled.')
                                    ->columnSpanFull(),

                                Repeater::make('options')
                                    ->label('Multiple Choice Options (Select the single correct answer)')
                                    ->schema([

                                        TextInput::make('option_text')
                                            ->label('Choice Text')
                                            ->placeholder('e.g. 4.18 Joules')
                                            ->required()
                                            ->columnSpan(3),

                                        Toggle::make('is_correct')
                                            ->label('Correct Answer')
                                            ->default(false)
                                            ->inline(false)
                                            ->columnSpan(1),

                                    ])
                                    ->columns(4)
                                    ->minItems(2)
                                    ->maxItems(6)
                                    ->defaultItems(4)
                                    ->reorderable(false)
                                    ->addActionLabel('＋ Add Option')
                                    ->itemLabel(function (array $state): string {
                                        $opt = trim($state['option_text'] ?? '');
                                        $isCorrect = (bool) ($state['is_correct'] ?? false);

                                        return filled($opt)
                                            ? ($isCorrect ? '✓ [CORRECT] ' : '○ ').Str::limit($opt, 40)
                                            : 'Option';
                                    })
                                    ->columnSpanFull(),

                            ])
                            ->columns(4)
                            ->collapsible()
                            ->collapsed(false)
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->addActionLabel('＋ Add Another Question')
                            ->itemLabel(function (array $state): string {
                                $txt = trim(strip_tags($state['question_text'] ?? ''));
                                $pts = (int) ($state['points'] ?? 1);
                                $ptsLabel = $pts === 1 ? '1 mark' : "{$pts} marks";

                                return filled($txt)
                                    ? Str::limit($txt, 70)." ({$ptsLabel})"
                                    : 'New Question';
                            })
                            ->minItems(1)
                            ->live()
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}
