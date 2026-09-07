<?php

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function sbUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function sbTeacherByNo(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

test('sidebar shows school logo images for schools that have one', function () {
    $this->seed();
    Storage::disk((string) config('filament.default_filesystem_disk', 'local'))
        ->put('schools/logos/test-logo.jpg', 'fake-logo-contents');

    $teacher = sbTeacherByNo('EMP-T-1001');
    $school = $teacher->schools()->firstOrFail();
    $school->update(['logo' => 'schools/logos/test-logo.jpg']);

    $this->actingAs($teacher->user);

    $response = $this->get('/teacher');
    $response->assertOk();

    $html = $response->getContent();
    expect($html)->toContain('school-logo')
        ->and($html)->toContain('<img src="'.e($school->logo_url).'"');
});

test('sidebar falls back to the first letter when a school has no logo', function () {
    $this->seed();
    $teacher = sbTeacherByNo('EMP-T-1001');

    $school = $teacher->schools()->firstOrFail();
    $school->update(['logo' => null]);
    $this->actingAs($teacher->user);

    $response = $this->get('/teacher');
    $response->assertOk();

    $html = $response->getContent();
    expect($html)->toContain('<span class="school-logo">')
        ->and($html)->toContain(strtoupper(substr($school->name, 0, 1)))
        ->and($html)->not->toContain('/schools/'.$school->getKey().'/logo');
});

test('school logo endpoint serves the stored file and requires auth', function () {
    $this->seed();
    Storage::disk((string) config('filament.default_filesystem_disk', 'local'))
        ->put('schools/logos/test-logo.jpg', 'fake-logo-contents');

    $school = School::firstOrFail();
    $school->update(['logo' => 'schools/logos/test-logo.jpg']);

    $this->get('/schools/'.$school->getKey().'/logo')->assertRedirect();

    $this->actingAs(sbUser('teacher1@example.com'))
        ->get('/schools/'.$school->getKey().'/logo')
        ->assertOk();
});

test('school logo endpoint returns 404 when the school has no logo or file', function () {
    $this->seed();
    $school = School::firstOrFail();
    $school->update(['logo' => null]);

    $this->actingAs(sbUser('teacher1@example.com'))
        ->get('/schools/'.$school->getKey().'/logo')
        ->assertNotFound();

    $school->update(['logo' => 'schools/logos/missing.jpg']);

    $this->get('/schools/'.$school->getKey().'/logo')
        ->assertNotFound();
});

test('sidebar has notification tab leading to notification page', function () {
    $this->seed();
    $teacher = sbTeacherByNo('EMP-T-1001');
    $this->actingAs($teacher->user);

    $response = $this->get('/teacher');
    $response->assertOk();

    $html = $response->getContent();
    expect($html)->toContain('>Notifications<')
        ->and($html)->toContain(route('filament.teacher.pages.notifications'));

    $notificationsResponse = $this->get(route('filament.teacher.pages.notifications'));
    $notificationsResponse->assertOk();
    expect($notificationsResponse->getContent())->toContain('Notifications');
});
