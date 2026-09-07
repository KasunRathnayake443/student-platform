<?php

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function dbUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function dbTeacherByNo(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

test('dashboard shows three stat tiles and no students total', function () {
    $this->seed();
    $this->actingAs(dbUser('teacher1@example.com'));

    $html = $this->get('/teacher')->assertOk()->getContent();

    expect($html)->toContain('stat-icon-indigo')
        ->and($html)->toContain('stat-icon-emerald')
        ->and($html)->toContain('stat-icon-violet')
        ->and($html)->toContain('>Schools<')
        ->and($html)->toContain('>Grades<')
        ->and($html)->toContain('>Classes<')
        ->and($html)->not->toContain('Total enrolled');
});

test('dashboard uses the full available width', function () {
    $this->seed();
    $this->actingAs(dbUser('teacher1@example.com'));

    $html = $this->get('/teacher')->assertOk()->getContent();

    expect($html)->toContain('grid-template-columns:repeat(3,1fr)')
        ->and($html)->not->toContain('max-width:1400px');
});

test('dashboard school cards show logos with letter fallback', function () {
    $this->seed();
    Storage::disk((string) config('filament.default_filesystem_disk', 'local'))
        ->put('schools/logos/test-logo.jpg', 'fake-logo-contents');

    $teacher = dbTeacherByNo('EMP-T-1001');
    $school = $teacher->schools()->firstOrFail();

    $school->update(['logo' => 'schools/logos/test-logo.jpg']);
    $this->actingAs($teacher->user);

    $html = $this->get('/teacher')->assertOk()->getContent();
    expect($html)->toContain('<img src="'.e($school->fresh()->logo_url).'"');

    $school->update(['logo' => null]);

    $html = $this->get('/teacher')->assertOk()->getContent();
    expect($html)->toContain(strtoupper(substr($school->name, 0, 1)));
});

test('topbar shows the date pill and user identity chip', function () {
    $this->seed();
    $teacher = dbTeacherByNo('EMP-T-1001');
    $this->actingAs($teacher->user);

    $html = $this->get('/teacher')->assertOk()->getContent();

    expect($html)->toContain('t-date-pill')
        ->and($html)->toContain(now()->format('D, M j'))
        ->and($html)->toContain('t-tb-user')
        ->and($html)->toContain(e($teacher->user->name));
});

test('dashboard displays modern hero banner with greeting and quick action buttons', function () {
    $this->seed();
    $teacher = dbTeacherByNo('EMP-T-1001');
    $this->actingAs($teacher->user);

    $html = $this->get('/teacher')->assertOk()->getContent();

    expect($html)->toContain('t-hero')
        ->and($html)->toContain('Welcome back, '.e($teacher->user->name))
        ->and($html)->toContain('New Quiz')
        ->and($html)->toContain(route('filament.teacher.pages.notifications'));
});

test('dashboard renders action center and search filter for classes', function () {
    $this->seed();
    $teacher = dbTeacherByNo('EMP-T-1001');
    $this->actingAs($teacher->user);

    $html = $this->get('/teacher')->assertOk()->getContent();

    expect($html)->toContain('Action Center')
        ->and($html)->toContain('t-class-search')
        ->and($html)->toContain('Active Students')
        ->and($html)->toContain('Pending Submissions');
});
