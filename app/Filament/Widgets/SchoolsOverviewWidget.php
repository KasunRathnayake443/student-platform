<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\School;
use Filament\Widgets\Widget;

class SchoolsOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.schools-overview-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?int $selectedSchoolId = null;

    public ?int $selectedClassId = null;

    public ?int $selectedAssignmentId = null;

    public ?int $selectedSubmissionId = null;

    public ?int $selectedLessonId = null;

    public function mount(): void
    {
        if ($this->selectedSchoolId === null) {
            $firstSchool = School::orderBy('name')->first();
            $this->selectedSchoolId = $firstSchool?->id;
        }
    }

    public function selectSchool(int $schoolId): void
    {
        $this->selectedSchoolId = $schoolId;
        $this->selectedClassId = null;
        $this->selectedAssignmentId = null;
        $this->selectedSubmissionId = null;
        $this->selectedLessonId = null;
    }

    public function selectClass(int $classId): void
    {
        $this->selectedClassId = $classId;
        $this->selectedSubmissionId = null;
        $this->selectedLessonId = null;
    }

    public function selectAssignment(int $assignmentId): void
    {
        $this->selectedAssignmentId = $assignmentId;
        $this->selectedSubmissionId = null;
        $this->selectedLessonId = null;
    }

    public function selectSubmission(int $submissionId): void
    {
        $this->selectedSubmissionId = $submissionId;
    }

    public function selectLesson(int $lessonId): void
    {
        $this->selectedLessonId = $lessonId;
    }

    public function closeDetails(): void
    {
        $this->selectedClassId = null;
        $this->selectedAssignmentId = null;
        $this->selectedSubmissionId = null;
        $this->selectedLessonId = null;
    }

    public function getSchoolsProperty()
    {
        return School::orderBy('name')->get();
    }

    public function getSelectedSchoolProperty()
    {
        if (! $this->selectedSchoolId) {
            return null;
        }

        return School::with(['grades' => function ($q) {
            $q->orderBy('name')->withCount(['learningClasses', 'students']);
        }])->find($this->selectedSchoolId);
    }

    public function getSelectedSchoolClassesProperty()
    {
        if (! $this->selectedSchoolId) {
            return collect();
        }

        return LearningClass::whereHas('grade', function ($query) {
            $query->where('school_id', $this->selectedSchoolId);
        })
            ->with(['grade', 'assignments', 'lessons'])
            ->withCount(['students', 'teachers', 'assignments', 'lessons'])
            ->orderBy('name')
            ->get();
    }

    public function getSelectedSchoolAssignmentsProperty()
    {
        if (! $this->selectedSchoolId) {
            return collect();
        }

        return Assignment::whereHas('learningClass.grade', function ($query) {
            $query->where('school_id', $this->selectedSchoolId);
        })
            ->with(['learningClass.grade', 'teacher.user'])
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSelectedSchoolLessonsProperty()
    {
        if (! $this->selectedSchoolId) {
            return collect();
        }

        return Lesson::whereHas('learningClass.grade', function ($query) {
            $query->where('school_id', $this->selectedSchoolId);
        })
            ->with(['learningClass.grade', 'teacher.user', 'attachments'])
            ->withCount('attachments')
            ->orderBy('sort_order')
            ->get();
    }

    public function getSelectedClassProperty()
    {
        if (! $this->selectedClassId) {
            return null;
        }

        return LearningClass::with([
            'grade.school',
            'teachers.user',
            'students.user',
            'lessons.attachments',
            'assignments.submissions.student.user',
        ])
            ->withCount(['students', 'teachers', 'lessons', 'assignments'])
            ->find($this->selectedClassId);
    }

    public function getSelectedAssignmentProperty()
    {
        if (! $this->selectedAssignmentId) {
            return null;
        }

        return Assignment::with([
            'learningClass.grade.school',
            'teacher.user',
            'attachments',
            'submissions.student.user',
            'submissions.attachments',
        ])
            ->withCount('submissions')
            ->find($this->selectedAssignmentId);
    }

    public function getSelectedSubmissionProperty()
    {
        if (! $this->selectedSubmissionId) {
            return null;
        }

        return AssignmentSubmission::with([
            'student.user',
            'assignment.learningClass',
            'attachments',
            'grader.user',
        ])->find($this->selectedSubmissionId);
    }

    public function getSelectedLessonProperty()
    {
        if (! $this->selectedLessonId) {
            return null;
        }

        return Lesson::with([
            'learningClass.grade.school',
            'teacher.user',
            'attachments',
        ])->find($this->selectedLessonId);
    }

    protected function getViewData(): array
    {
        return [
            'schools' => $this->schools,
            'selectedSchool' => $this->selectedSchool,
            'classes' => $this->selectedSchoolClasses,
            'assignments' => $this->selectedSchoolAssignments,
            'lessons' => $this->selectedSchoolLessons,
            'selectedClass' => $this->selectedClass,
            'selectedAssignment' => $this->selectedAssignment,
            'selectedSubmission' => $this->selectedSubmission,
            'selectedLesson' => $this->selectedLesson,
        ];
    }
}
