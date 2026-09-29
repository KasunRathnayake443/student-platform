<?php

namespace App\Filament\Teacher\Pages;

use App\Models\Assignment;
use App\Models\LearningClass;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\SchoolEmailNotificationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @property-read Schema $form
 */
class ComposeStudentEmail extends Page
{
    protected string $view = 'filament.teacher.pages.compose-student-email';

    protected static ?string $title = 'Email Students';

    protected static ?string $navigationLabel = 'Email Students';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 1;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getLayout(): string
    {
        return 'filament.teacher.layouts.app';
    }

    public function mount(): void
    {
        $this->form->fill([
            'include_parents' => true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Recipients')
                    ->description('Pick one of your classes, then choose the students to email.')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Select::make('learning_class_id')
                            ->label('Learning Class')
                            ->options(fn (): array => $this->classOptions())
                            ->searchable()
                            ->live()
                            ->required(),

                        Select::make('student_ids')
                            ->label('Students')
                            ->options(fn (Get $get): array => $this->studentOptions($get('learning_class_id')))
                            ->searchable()
                            ->multiple()
                            ->required()
                            ->minItems(1)
                            ->live()
                            ->helperText('Only students with an active enrolment in the selected class can be selected.'),

                        Toggle::make('include_parents')
                            ->label('Also send to parent email')
                            ->helperText('Skipped automatically for any student without a parent email on file.')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Message')
                    ->description('Your message, plus optional assignment and quiz scores for each student.')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->maxLength(2000)
                            ->rows(5)
                            ->columnSpanFull(),

                        Select::make('assignment_id')
                            ->label('Include assignment submission (optional)')
                            ->options(fn (Get $get): array => $this->assignmentOptions($get('learning_class_id')))
                            ->searchable()
                            ->live(),

                        Select::make('quiz_id')
                            ->label('Include quiz score (optional)')
                            ->options(fn (Get $get): array => $this->quizOptions($get('learning_class_id')))
                            ->searchable()
                            ->live(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('send')
            ->footer([
                Actions::make([
                    Action::make('send')
                        ->label('Send Email')
                        ->icon('heroicon-o-paper-airplane')
                        ->submit('send'),
                ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    protected function classOptions(): array
    {
        $teacher = auth()->user()?->teacher;

        if (! $teacher) {
            return [];
        }

        return LearningClass::query()
            ->with('grade')
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->getKey()))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (LearningClass $class): array => [
                $class->getKey() => trim(($class->grade->name ?? '').' · '.($class->name ?? ''), ' ·'),
            ])
            ->all();
    }

    protected function teacherOwnsClass(mixed $classId): ?LearningClass
    {
        $teacher = auth()->user()?->teacher;

        if (! $teacher || ! $classId) {
            return null;
        }

        return LearningClass::query()
            ->whereKey($classId)
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->getKey()))
            ->first();
    }

    /**
     * @return Collection<int, Student>
     */
    protected function activeStudents(?LearningClass $class): Collection
    {
        $schoolId = $class instanceof LearningClass
            ? $class->grade?->school_id
            : null;

        if (! $schoolId) {
            return collect();
        }

        return $class->enrollments()
            ->where('student_enrollments.school_id', $schoolId)
            ->where('student_enrollments.status', 'active')
            ->whereHas('student', fn (Builder $query): Builder => $query->whereHas('user'))
            ->with('student.user')
            ->get()
            ->map(fn (StudentEnrollment $enrollment): ?Student => $enrollment->student)
            ->filter(fn (?Student $student): bool => $student instanceof Student)
            ->unique(fn (Student $student): int => (int) $student->getKey())
            ->values();
    }

    /**
     * @return array<int, string>
     */
    protected function studentOptions(mixed $classId): array
    {
        $class = $this->teacherOwnsClass($classId);

        return $this->activeStudents($class)
            ->sortBy('admission_no')
            ->mapWithKeys(function (Student $student): array {
                $name = trim((string) $student->user->name);

                return [
                    $student->getKey() => $name === ''
                        ? $student->admission_no
                        : $name.' ('.$student->admission_no.')',
                ];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function assignmentOptions(mixed $classId): array
    {
        $class = $this->teacherOwnsClass($classId);

        if (! $class instanceof LearningClass) {
            return [];
        }

        return Assignment::query()
            ->where('learning_class_id', $class->getKey())
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function quizOptions(mixed $classId): array
    {
        $class = $this->teacherOwnsClass($classId);

        if (! $class instanceof LearningClass) {
            return [];
        }

        return Quiz::query()
            ->where('learning_class_id', $class->getKey())
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $class = $this->teacherOwnsClass($data['learning_class_id'] ?? null);

        if (! $class instanceof LearningClass) {
            Notification::make()
                ->title('Select one of your own classes first.')
                ->danger()
                ->send();

            return;
        }

        $students = $this->activeStudents($class);

        $requested = array_map('intval', (array) ($data['student_ids'] ?? []));

        $selected = $students
            ->filter(fn (Student $student): bool => in_array((int) $student->getKey(), $requested, true));

        if ($selected->isEmpty()) {
            Notification::make()
                ->title('Select at least one active student in the chosen class.')
                ->danger()
                ->send();

            return;
        }

        $dispatched = app(SchoolEmailNotificationService::class)->sendCustomStudentMessage(
            $class,
            $selected,
            trim((string) ($data['message'] ?? '')),
            $this->assignmentForClass($data['assignment_id'] ?? null, $class),
            $this->quizForClass($data['quiz_id'] ?? null, $class),
            (bool) ($data['include_parents'] ?? true),
            auth()->user()?->name,
        );

        if ($dispatched === 0) {
            Notification::make()
                ->title('No email was sent.')
                ->body('Either the school has no working SMTP settings, or none of the selected students have a reachable email address.')
                ->danger()
                ->send();

            return;
        }

        $this->form->fill([
            'learning_class_id' => $class->getKey(),
            'include_parents' => (bool) ($data['include_parents'] ?? true),
        ]);

        Notification::make()
            ->title("Email queued for {$dispatched} recipient(s).")
            ->body('A queue worker must be running for the messages to be delivered.')
            ->success()
            ->send();
    }

    protected function assignmentForClass(mixed $assignmentId, LearningClass $class): ?Assignment
    {
        if (! $assignmentId) {
            return null;
        }

        return Assignment::query()
            ->whereKey($assignmentId)
            ->where('learning_class_id', $class->getKey())
            ->first();
    }

    protected function quizForClass(mixed $quizId, LearningClass $class): ?Quiz
    {
        if (! $quizId) {
            return null;
        }

        return Quiz::query()
            ->whereKey($quizId)
            ->where('learning_class_id', $class->getKey())
            ->first();
    }
}
