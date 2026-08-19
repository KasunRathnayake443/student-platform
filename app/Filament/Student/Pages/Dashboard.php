<?php

namespace App\Filament\Student\Pages;

use App\Models\Student;
use App\Services\StudentContextService;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    // Override layout to be a bare base wrapper (no Filament topbar/sidebar/margins)
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.dashboard';

    public string $tier = 'junior';

    public ?array $activeContext = null;

    public $allContexts = null;

    public ?Student $student = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->student = $user?->student;

        if (! $this->student) {
            return;
        }

        // Detect age tier
        $this->tier = $this->student->getAgeTier();

        // Load contexts
        $service = app(StudentContextService::class);
        $this->activeContext = $service->getActiveContext($this->student);
        $this->allContexts = $service->getContextsGroupedBySchool($this->student);
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

    protected function getViewData(): array
    {
        return [
            'tier' => $this->tier,
            'student' => $this->student,
            'activeContext' => $this->activeContext,
            'allContexts' => $this->allContexts,
            'firstName' => explode(' ', $this->student?->user?->name ?? 'Student')[0],
        ];
    }
}
