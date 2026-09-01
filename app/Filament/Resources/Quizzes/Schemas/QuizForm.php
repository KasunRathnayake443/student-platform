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
                | 1. Quiz Details & Assignment
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Information')
                    ->description('Set quiz title, target learning class, and responsible teachers.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([

                        TextInput::make('title')
                            ->label('Quiz Title')
                            ->placeholder('e.g. Unit 4: Thermodynamics & Newton’s Laws')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

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
                            ->afterStateUpdated(function (Set $set) use ($isTeacher): void {
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
                            ),

                        Textarea::make('description')
                            ->label('Short Description (Optional)')
                            ->placeholder('Brief summary of topics covered in this quiz...')
                            ->rows(2)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        RichEditor::make('instructions')
                            ->label('Instructions & Rules for Students (Optional)')
                            ->helperText('Shown to students before starting the quiz.')
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 2. Timing, Scoring & Availability
                |--------------------------------------------------------------------------
                */

                Section::make('Quiz Rules & Timing')
                    ->description('Configure attempts, passing score, time limits, and availability.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([

                        TextInput::make('time_limit_minutes')
                            ->label('Time Limit (Minutes)')
                            ->placeholder('Untimed (Leave blank)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->suffix('min')
                            ->helperText('Leave empty for no time limit.'),

                        TextInput::make('max_attempts')
                            ->label('Max Attempts')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->helperText('Allowed attempts per student (default: 1).'),

                        TextInput::make('passing_percentage')
                            ->label('Passing Percentage')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(50)
                            ->suffix('%')
                            ->required()
                            ->helperText('Minimum score to pass (default: 50%).'),

                        DateTimePicker::make('end_at')
                            ->label('End Date & Time (Deadline)')
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->after('start_at')
                            ->helperText('Quiz closes after this deadline.'),

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
                            ->helperText('Students can start taking quiz right away.'),

                        Toggle::make('is_published')
                            ->label('Published to Students')
                            ->default(true)
                            ->helperText('Visible to enrolled students when published.'),

                        Toggle::make('show_correct_answers_after_submission')
                            ->label('Show Correct Answers')
                            ->default(true)
                            ->helperText('Show answer key & feedback upon completion.'),

                        Toggle::make('shuffle_questions')
                            ->label('Shuffle Questions')
                            ->default(false)
                            ->helperText('Randomize question sequence per attempt.'),

                        Toggle::make('shuffle_options')
                            ->label('Shuffle Choices')
                            ->default(false)
                            ->helperText('Randomize answer options for each question.'),

                        TextInput::make('availability_type')
                            ->hidden()
                            ->dehydrated(true)
                            ->default('immediate'),

                        DateTimePicker::make('start_at')
                            ->label('Scheduled Start Date & Time')
                            ->seconds(false)
                            ->native(false)
                            ->required(fn (Get $get): bool => ! (bool) $get('available_immediately'))
                            ->hidden(fn (Get $get): bool => (bool) $get('available_immediately'))
                            ->columnSpanFull(),

                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 3. Quick CSV Import Banner (Prominent & Apparent)
                |--------------------------------------------------------------------------
                */

                Section::make('⚡ Quick Import Questions from CSV')
                    ->description('Save time by uploading a questions CSV file. Questions will automatically load below for editing.')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([

                        Placeholder::make('csv_import_banner')
                            ->hiddenLabel()
                            ->content(
                                fn () => new HtmlString(
                                    '<div class="rounded-xl border-2 border-indigo-200 bg-gradient-to-r from-indigo-50 to-blue-50 p-4 dark:border-indigo-800 dark:from-indigo-950/60 dark:to-blue-950/60 shadow-sm">'
                                    .'<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">'
                                    .'<div>'
                                    .'<h4 class="text-base font-bold text-indigo-900 dark:text-indigo-200 flex items-center gap-1.5">'
                                    .'<span>⚡ Fast Bulk Import from CSV (Optional & Instant)</span>'
                                    .'</h4>'
                                    .'<p class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">'
                                    .'Upload a CSV file and questions will automatically load into the Question Builder below where you can review, edit text, attach images/videos, and adjust choices.'
                                    .'</p>'
                                    .'</div>'
                                    .'<a href="'.e(route('quiz-questions.import.template')).'" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition whitespace-nowrap">'
                                    .'📥 Download CSV Template'
                                    .'</a>'
                                    .'</div>'
                                    .'</div>'
                                )
                            )
                            ->columnSpanFull(),

                        FileUpload::make('import_file')
                            ->label('Upload Questions CSV File (Auto-Populates Questions Below)')
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
                                            ->body('All questions have been populated into the Question Builder below. You can review, edit text, attach images or videos, and adjust choices before saving.')
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
                | 4. Questions & Options Builder (Distinct Separation Between Questions)
                |--------------------------------------------------------------------------
                */

                Section::make('Questions & Multiple-Choice Answers')
                    ->description('Add questions, attach optional images or videos, and configure choices with 1 correct answer.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([

                        Repeater::make('questions')
                            ->label('')
                            ->schema([

                                Placeholder::make('q_title_bar')
                                    ->hiddenLabel()
                                    ->content(fn (Get $get) => new HtmlString(
                                        '<div class="flex items-center justify-between bg-slate-100 dark:bg-slate-800 border-l-4 border-primary-600 rounded-r-lg px-4 py-2.5 shadow-xs -mt-1 -mx-1">'
                                        .'<span class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">📝 Question Details & Media</span>'
                                        .'</div>'
                                    ))
                                    ->columnSpanFull(),

                                TextInput::make('question_text')
                                    ->label('Question Prompt')
                                    ->placeholder('Type the question or problem statement here...')
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('points')
                                    ->label('Marks / Points')
                                    ->numeric()
                                    ->integer()
                                    ->default(1)
                                    ->minValue(1)
                                    ->suffix('marks')
                                    ->required()
                                    ->columnSpan(1),

                                FileUpload::make('question_image')
                                    ->label('🖼️ Question Image (Optional)')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                    ->image()
                                    ->disk((string) config('filament.default_filesystem_disk', 'local'))
                                    ->directory('quiz_questions/images')
                                    ->maxSize(5120)
                                    ->helperText('Attach a diagram or picture (JPEG, PNG, WebP - max 5 MB).')
                                    ->columnSpan(2),

                                FileUpload::make('question_video')
                                    ->label('🎥 Question Video (Optional)')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/*'])
                                    ->disk((string) config('filament.default_filesystem_disk', 'local'))
                                    ->directory('quiz_questions/videos')
                                    ->maxSize(20480)
                                    ->helperText('Attach a video clip (MP4, WebM, MOV - max 20 MB).')
                                    ->columnSpan(2),

                                Textarea::make('explanation')
                                    ->label('💡 Explanation / Solution Feedback (Optional)')
                                    ->placeholder('Explain why the correct answer is right (shown to students when reviewing results)...')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Placeholder::make('choices_bar')
                                    ->hiddenLabel()
                                    ->content(fn () => new HtmlString(
                                        '<div class="flex items-center justify-between bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 rounded-r-lg px-4 py-2 mt-2 -mx-1">'
                                        .'<span class="font-bold text-xs text-emerald-800 dark:text-emerald-200">Multiple-Choice Answer Options (Check the single correct answer)</span>'
                                        .'</div>'
                                    ))
                                    ->columnSpanFull(),

                                Repeater::make('options')
                                    ->label('')
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

                                Placeholder::make('q_bottom_divider')
                                    ->hiddenLabel()
                                    ->content(fn () => new HtmlString('<div class="w-full border-b-2 border-dashed border-gray-300 dark:border-gray-700 my-2"></div>'))
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
                                    ? '❓ '.Str::limit($txt, 65)." ({$ptsLabel})"
                                    : '❓ New Question';
                            })
                            ->minItems(1)
                            ->live()
                            ->columnSpanFull(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}
