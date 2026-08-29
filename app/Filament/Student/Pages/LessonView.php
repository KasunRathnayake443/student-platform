<?php

namespace App\Filament\Student\Pages;

use App\Models\Lesson;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LessonView extends Page
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.lesson-view';

    protected static ?string $slug = 'lesson';

    public ?Lesson $lesson = null;

    public string $tier = 'junior';

    public ?string $schoolName = null;

    public ?string $gradeName = null;

    public ?string $className = null;

    public ?string $teacherName = null;

    public ?int $lessonNumber = null;

    public ?int $lessonTotal = null;

    public function mount(): void
    {
        $user = Auth::user();
        $student = $user?->student;

        if (! $student) {
            $this->redirect('/student');
            return;
        }

        $lessonId = (int) request()->query('lesson');

        /** @var Lesson|null $lesson */
        $lesson = Lesson::with([
            'learningClass.grade.school',
            'learningClass.teachers.user',
            'teacher.user',
            'attachments',
        ])
            ->where('is_published', true)
            ->find($lessonId);

        if (! $lesson) {
            abort(404);
        }

        // The student must be enrolled in the lesson's class before viewing it.
        $enrollments = $student->enrollments()
            ->with(['school', 'grade', 'classes'])
            ->where('status', 'active')
            ->get();

        $enrolledInClass = $enrollments->contains(
            fn ($enrollment) => $enrollment->classes->contains('id', $lesson->learning_class_id)
        );

        if (! $enrolledInClass) {
            abort(403);
        }

        $class = $lesson->learningClass;

        $this->lesson = $lesson;
        $this->tier = $student->getAgeTier();
        $this->schoolName = $class?->grade?->school?->name ?? 'My School';
        $this->gradeName = $class?->grade?->name ?? null;
        $this->className = $class?->name ?? 'General Class';
        $this->teacherName = $lesson->teacher?->user?->name
            ?? $class?->teachers->first()?->user?->name
            ?? 'Your Teacher';

        $published = $class
            ? $class->lessons()->where('is_published', true)->orderBy('sort_order')->get()
            : collect();

        $this->lessonTotal = $published->count();
        $this->lessonNumber = $published->contains('id', $lesson->id)
            ? $published->search(fn ($item) => $item->id === $lesson->id) + 1
            : null;
    }

    public function getTitle(): string
    {
        return $this->lesson?->title ?? 'Lesson';
    }
}