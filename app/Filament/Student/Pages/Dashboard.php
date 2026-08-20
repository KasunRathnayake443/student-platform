<?php

namespace App\Filament\Student\Pages;

use App\Models\Student;
use App\Models\Assignment;
use App\Services\StudentContextService;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected string $view = 'filament.student.pages.dashboard';

    public string $activeTab = 'dashboard';

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

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;

        Notification::make()
            ->title('Navigation')
            ->body('Switched to ' . ucfirst($tab) . ' section.')
            ->info()
            ->send();
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

        Notification::make()
            ->title('Context Switched!')
            ->body('Now viewing ' . ($this->activeContext['school']->name ?? 'School') . ' - ' . ($this->activeContext['grade']->name ?? 'Grade'))
            ->success()
            ->send();
    }

    public function launchAssignment(?int $id = null): void
    {
        $assignment = $id ? Assignment::find($id) : null;
        $title = $assignment ? $assignment->title : 'Assignment Workspace';

        Notification::make()
            ->title('🚀 Mission Started: ' . $title)
            ->body('Opening your interactive assignment workspace...')
            ->info()
            ->send();
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/student/login');
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
