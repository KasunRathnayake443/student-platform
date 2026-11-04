<?php

use App\Filament\Widgets\EnrollmentTrendChart;
use App\Filament\Widgets\LatestSchoolsTableWidget;
use App\Filament\Widgets\PlatformResourceDistributionChart;
use App\Filament\Widgets\PlatformStatsOverviewWidget;
use App\Filament\Widgets\RecentQuizAttemptsTableWidget;
use App\Filament\Widgets\SchoolsOverviewWidget;
use App\Filament\Widgets\SuperAdminWelcomeWidget;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LearningClass;
use App\Models\Lesson;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('super admin dashboard page renders', function () {
    $admin = User::where('email', 'admin1@example.com')->firstOrFail();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Super Admin Dashboard');
});

test('super admin dashboard widgets render responsively', function () {
    $admin = User::where('email', 'admin1@example.com')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SuperAdminWelcomeWidget::class)
        ->assertOk()
        ->assertSee('sa-welcome-action-buttons', false);

    Livewire::actingAs($admin)
        ->test(PlatformStatsOverviewWidget::class)
        ->assertOk();

    Livewire::actingAs($admin)
        ->test(SchoolsOverviewWidget::class)
        ->assertOk()
        ->assertSee('so-school-tabs', false)
        ->assertSee('so-grid', false)
        ->assertSee('so-modal', false);

    Livewire::actingAs($admin)
        ->test(EnrollmentTrendChart::class)
        ->assertOk();

    Livewire::actingAs($admin)
        ->test(PlatformResourceDistributionChart::class)
        ->assertOk();

    Livewire::actingAs($admin)
        ->test(LatestSchoolsTableWidget::class)
        ->assertOk();

    Livewire::actingAs($admin)
        ->test(RecentQuizAttemptsTableWidget::class)
        ->assertOk();
});

test('schools overview modals render responsive markup', function () {
    $admin = User::where('email', 'admin1@example.com')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(SchoolsOverviewWidget::class)
        ->call('selectClass', LearningClass::firstOrFail()->id)
        ->assertOk()
        ->assertSee('so-modal-overlay', false)
        ->assertSee('so-modal-title-wrap', false)
        ->assertSee('so-modal-list-row', false);

    Livewire::actingAs($admin)
        ->test(SchoolsOverviewWidget::class)
        ->call('selectAssignment', Assignment::firstOrFail()->id)
        ->assertOk()
        ->assertSee('so-modal-grid', false)
        ->assertSee('so-modal-footer', false);

    Livewire::actingAs($admin)
        ->test(SchoolsOverviewWidget::class)
        ->call('selectLesson', Lesson::firstOrFail()->id)
        ->assertOk()
        ->assertSee('so-modal', false);

    Livewire::actingAs($admin)
        ->test(SchoolsOverviewWidget::class)
        ->call('selectSubmission', AssignmentSubmission::firstOrFail()->id)
        ->assertOk()
        ->assertSee('so-modal', false);
});
