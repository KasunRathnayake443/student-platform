<?php

use App\Filament\Resources\Students\Pages\CreateStudent as AdminCreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent as AdminEditStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\CreateStudent as SchoolAdminCreateStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\EditStudent as SchoolAdminEditStudent;
use App\Models\Student;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function parentEmailUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function parentEmailStudent(string $email): Student
{
    return Student::whereHas('user', fn ($query) => $query->where('email', $email))->firstOrFail();
}

test('the parent email is optional on the admin create student form', function () {
    $this->seed();
    $admin = parentEmailUser('admin1@example.com');
    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminCreateStudent::class)
        ->fillForm([
            'name' => 'No Parent Student',
            'email' => 'noparent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'admission_no' => 'ADM-NOPARENT',
            'parent_name' => 'Alex Parent',
            'parent_phone' => '+94 77 000 0001',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $student = Student::where('admission_no', 'ADM-NOPARENT')->firstOrFail();

    expect($student->parent_name)->toBe('Alex Parent')
        ->and($student->parent_email)->toBeNull();
});

test('the admin create student form stores a parent email when provided', function () {
    $this->seed();
    $admin = parentEmailUser('admin1@example.com');
    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminCreateStudent::class)
        ->fillForm([
            'name' => 'With Parent Student',
            'email' => 'withparent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'admission_no' => 'ADM-WITHPARENT',
            'parent_name' => 'Jordan Parent',
            'parent_email' => 'jordan.parent@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $student = Student::where('admission_no', 'ADM-WITHPARENT')->firstOrFail();

    expect($student->parent_email)->toBe('jordan.parent@example.com');
});

test('the parent email field rejects an invalid address', function () {
    $this->seed();
    $admin = parentEmailUser('admin1@example.com');
    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminCreateStudent::class)
        ->fillForm([
            'name' => 'Bad Parent Student',
            'email' => 'badparent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'admission_no' => 'ADM-BADPARENT',
            'parent_email' => 'not-an-email',
        ])
        ->call('create')
        ->assertHasFormErrors(['parent_email']);

    expect(Student::where('admission_no', 'ADM-BADPARENT')->exists())->toBeFalse();
});

test('the admin edit student form updates the parent email', function () {
    $this->seed();
    $admin = parentEmailUser('admin1@example.com');
    $student = parentEmailStudent('student1@example.com');
    Filament::setCurrentPanel('admin');

    expect($student->parent_email)->toBeNull();

    Livewire::actingAs($admin)
        ->test(AdminEditStudent::class, ['record' => $student->getKey()])
        ->assertFormSet(['parent_email' => null])
        ->fillForm(['parent_email' => 'michael.miller@example.com'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($student->fresh()->parent_email)->toBe('michael.miller@example.com');
});

test('the school admin edit student form updates the parent email', function () {
    $this->seed();
    $schoolAdmin = parentEmailUser('schooladmin1@example.com');
    $student = parentEmailStudent('student1@example.com');
    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditStudent::class, ['record' => $student->getKey()])
        ->fillForm(['parent_email' => 'elizabeth.williams@example.com'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($student->fresh()->parent_email)->toBe('elizabeth.williams@example.com');
});

test('the school admin create student form stores a parent email', function () {
    $this->seed();
    $schoolAdmin = parentEmailUser('schooladmin1@example.com');
    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminCreateStudent::class)
        ->fillForm([
            'name' => 'School Admin Parent Student',
            'email' => 'schooladminparent@example.com',
            'admission_no' => 'ADM-SAPARENT',
            'parent_name' => 'Sam Parent',
            'parent_email' => 'sam.parent@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Student::where('admission_no', 'ADM-SAPARENT')->firstOrFail()->parent_email)
        ->toBe('sam.parent@example.com');
});

test('clearing the parent email removes it again', function () {
    $this->seed();
    $admin = parentEmailUser('admin1@example.com');
    $student = parentEmailStudent('student1@example.com');
    $student->update(['parent_email' => 'temporary@example.com']);
    Filament::setCurrentPanel('admin');

    Livewire::actingAs($admin)
        ->test(AdminEditStudent::class, ['record' => $student->getKey()])
        ->fillForm(['parent_email' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($student->fresh()->parent_email)->toBeNull();
});
