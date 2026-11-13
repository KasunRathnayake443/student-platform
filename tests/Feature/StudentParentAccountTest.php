<?php

use App\Filament\Resources\Students\Pages\CreateStudent as AdminCreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent as AdminEditStudent;
use App\Filament\SchoolAdmin\Resources\Students\Pages\CreateStudent as SchoolAdminCreateStudent;
use App\Models\Student;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function spaParentUser(string $name, string $email): User
{
    $parent = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make('12345678'),
        'must_change_password' => false,
    ]);

    $parent->assignRole(Role::firstOrCreate([
        'name' => 'parent',
        'guard_name' => 'web',
    ]));

    return $parent;
}

function spaAdmin(): User
{
    return User::where('email', 'admin1@example.com')->firstOrFail();
}

function spaStudent(string $email): Student
{
    return Student::whereHas('user', fn ($query) => $query->where('email', $email))->firstOrFail();
}

test('a super admin can create a new parent account while creating a student', function () {
    $this->seed();
    Filament::setCurrentPanel('admin');

    Livewire::actingAs(spaAdmin())
        ->test(AdminCreateStudent::class)
        ->assertFormFieldExists('parent_account_mode')
        ->fillForm([
            'name' => 'New Child',
            'email' => 'new.child@example.com',
            'password' => 'password123',
            'admission_no' => 'ADM-PARENT-001',
            'parent_account_mode' => 'new',
            'parent_account_name' => 'New Parent',
            'parent_account_email' => 'new.parent@example.com',
            'parent_account_password' => 'parent12345',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $student = spaStudent('new.child@example.com');
    $parent = User::where('email', 'new.parent@example.com')->firstOrFail();

    expect($student->parent_id)->toBe($parent->id)
        ->and($parent->hasRole('parent'))->toBeTrue()
        ->and($parent->children()->whereKey($student->id)->exists())->toBeTrue();
});

test('a super admin can link an existing parent account to a student', function () {
    $this->seed();
    Filament::setCurrentPanel('admin');

    $parent = spaParentUser('Existing Parent', 'existing.parent@example.com');
    $student = spaStudent('student1@example.com');

    Livewire::actingAs(spaAdmin())
        ->test(AdminEditStudent::class, ['record' => $student->getKey()])
        ->assertFormSet(['parent_account_mode' => 'none'])
        ->fillForm([
            'parent_account_mode' => 'existing',
            'parent_id' => $parent->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($student->fresh()->parent_id)->toBe($parent->id);
});

test('a super admin can create a parent account while editing a student', function () {
    $this->seed();
    Filament::setCurrentPanel('admin');

    $student = spaStudent('student1@example.com');

    Livewire::actingAs(spaAdmin())
        ->test(AdminEditStudent::class, ['record' => $student->getKey()])
        ->fillForm([
            'parent_account_mode' => 'new',
            'parent_account_name' => 'Added Parent',
            'parent_account_email' => 'added.parent@example.com',
            'parent_account_password' => 'parent12345',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $parent = User::where('email', 'added.parent@example.com')->firstOrFail();

    expect($student->fresh()->parent_id)->toBe($parent->id)
        ->and($parent->hasRole('parent'))->toBeTrue();
});

test('a super admin can unlink a parent account from a student', function () {
    $this->seed();
    Filament::setCurrentPanel('admin');

    $parent = spaParentUser('Existing Parent', 'existing.parent@example.com');
    $student = spaStudent('student1@example.com');
    $student->update(['parent_id' => $parent->id]);

    Livewire::actingAs(spaAdmin())
        ->test(AdminEditStudent::class, ['record' => $student->getKey()])
        ->assertFormSet([
            'parent_account_mode' => 'existing',
            'parent_id' => $parent->id,
        ])
        ->fillForm(['parent_account_mode' => 'none'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($student->fresh()->parent_id)->toBeNull();
});

test('a new parent account cannot reuse an existing user email', function () {
    $this->seed();
    Filament::setCurrentPanel('admin');

    Livewire::actingAs(spaAdmin())
        ->test(AdminCreateStudent::class)
        ->fillForm([
            'name' => 'Duplicate Parent Child',
            'email' => 'duplicate.child@example.com',
            'password' => 'password123',
            'admission_no' => 'ADM-PARENT-002',
            'parent_account_mode' => 'new',
            'parent_account_name' => 'Duplicate Parent',
            'parent_account_email' => 'admin1@example.com',
            'parent_account_password' => 'parent12345',
        ])
        ->call('create')
        ->assertHasFormErrors(['parent_account_email']);

    expect(Student::where('admission_no', 'ADM-PARENT-002')->exists())->toBeFalse();
});

test('the school admin student form does not expose parent account management', function () {
    $this->seed();
    Filament::setCurrentPanel('school-admin');

    $schoolAdmin = User::where('email', 'schooladmin1@example.com')->firstOrFail();

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminCreateStudent::class)
        ->assertFormFieldExists('parent_email')
        ->assertFormFieldDoesNotExist('parent_account_mode')
        ->assertFormFieldDoesNotExist('parent_id');
});
