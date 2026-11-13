<?php

use App\Filament\Parent\Pages\Auth\ParentLogin;
use App\Filament\Parent\Pages\Dashboard as ParentDashboard;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function ppParentUser(string $name, string $email): User
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

function ppStudentUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function ppStudent(string $email): Student
{
    return Student::whereHas('user', fn ($query) => $query->where('email', $email))->firstOrFail();
}

test('the parent login page renders and a parent can sign in', function () {
    $this->seed();
    ppParentUser('Parent One', 'parent.one@example.com');

    $this->get('/parent/login')
        ->assertOk()
        ->assertSee('Parent Portal')
        ->assertSee('Welcome back');

    Filament::setCurrentPanel('parent');

    Livewire::test(ParentLogin::class)
        ->fillForm([
            'email' => 'parent.one@example.com',
            'password' => '12345678',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->check())->toBeTrue();
});

test('only parent accounts can access the parent panel', function () {
    $this->seed();

    $parent = ppParentUser('Parent One', 'parent.one@example.com');
    $admin = User::where('email', 'admin1@example.com')->firstOrFail();
    $student = ppStudentUser('student1@example.com');

    expect($parent->canAccessPanel(Filament::getPanel('parent')))->toBeTrue()
        ->and($admin->canAccessPanel(Filament::getPanel('parent')))->toBeFalse()
        ->and($student->canAccessPanel(Filament::getPanel('parent')))->toBeFalse();

    $this->actingAs($admin)->get('/parent')->assertForbidden();
    $this->actingAs($parent)->get('/parent')->assertOk();
});

test('a parent dashboard shows every linked child across schools and only their data', function () {
    $this->seed();

    $parent = ppParentUser('Parent One', 'parent.one@example.com');
    $studentOne = ppStudent('student1@example.com');
    $studentFive = ppStudent('student5@example.com');

    $studentOne->update(['parent_id' => $parent->id]);
    $studentFive->update(['parent_id' => $parent->id]);

    $admin = User::where('email', 'admin1@example.com')->firstOrFail();

    app(NotificationService::class)->send($admin, [
        'title' => 'A note for Liam',
        'body' => 'Please check the latest quiz result.',
    ], [$studentOne->user_id]);

    $this->actingAs($parent);

    $this->get('/parent')
        ->assertOk()
        ->assertSee('Liam Ethan Miller')
        ->assertSee('Ethan Alexander Brown')
        ->assertSee('Quadratic Functions &amp; Algebra Mastery Quiz', false)
        ->assertSee('Quadratic Equations &amp; Parabolic Functions', false)
        ->assertSee('A note for Liam');

    $this->get('/parent?child='.$studentFive->id)
        ->assertOk()
        ->assertSee('Newton\'s Laws of Motion Assessment')
        ->assertSee('Ecosystem Dynamics &amp; Energy Pyramids', false)
        ->assertDontSee('A note for Liam');
});

test('switching children updates the dashboard data', function () {
    $this->seed();

    $parent = ppParentUser('Parent One', 'parent.one@example.com');
    $studentOne = ppStudent('student1@example.com');
    $studentFive = ppStudent('student5@example.com');

    $studentOne->update(['parent_id' => $parent->id]);
    $studentFive->update(['parent_id' => $parent->id]);

    Filament::setCurrentPanel('parent');

    Livewire::actingAs($parent)
        ->test(ParentDashboard::class)
        ->assertSee('Quadratic Functions & Algebra Mastery Quiz')
        ->call('selectChild', $studentFive->id)
        ->assertSee('Newton\'s Laws of Motion Assessment')
        ->assertDontSee('Quadratic Functions & Algebra Mastery Quiz');
});

test('a parent with no linked children sees a helpful empty state', function () {
    $this->seed();

    $parent = ppParentUser('Parent Without Children', 'parent.empty@example.com');

    $this->actingAs($parent)
        ->get('/parent')
        ->assertOk()
        ->assertSee('No students linked yet')
        ->assertSee('super administrator');
});
