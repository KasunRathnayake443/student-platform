<?php

namespace App\Filament\Student\Pages;

use App\Models\Student;
use App\Services\StudentContextService;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.dashboard';

    public string $activeTab = 'dashboard';

    public string $tier = 'junior';

    /** @var array<string, mixed>|null */
    public ?array $activeContext = null;

    /** @var Collection<int, array<string, mixed>>|null */
    public ?Collection $allContexts = null;

    public ?Student $student = null;

    public function mount(): void
    {
        $user = Auth::user();
        /** @var Student|null $student */
        $student = $user?->student;
        $this->student = $student;

        if (! $this->student instanceof Student) {
            return;
        }

        $this->tier = $this->student->getAgeTier();

        $service = app(StudentContextService::class);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function switchContext(string $key): void
    {
        if (! $this->student) {
            return;
        }

        $service = app(StudentContextService::class);
        $service->setActiveContext($this->student, $key);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/student/login');
    }

    public ?int $selectedClassId = null;

    public function viewClassLessons(int $classId): void
    {
        $this->selectedClassId = $classId;
    }

    public function closeClassLessons(): void
    {
        $this->selectedClassId = null;
    }

    protected function getViewData(): array
    {
        $selectedClass = $this->selectedClassId
            ? \App\Models\LearningClass::with([
                'lessons' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order'),
                'lessons.attachments',
                'teachers.user',
            ])->find($this->selectedClassId)
            : null;

        return [
            'tier' => $this->tier,
            'student' => $this->student,
            'activeContext' => $this->activeContext,
            'allContexts' => $this->allContexts,
            'firstName' => explode(' ', $this->student?->user->name ?? 'Student')[0],
            'selectedClass' => $selectedClass,
        ];
    }
}
