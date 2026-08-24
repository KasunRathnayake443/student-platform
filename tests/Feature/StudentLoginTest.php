<?php

use App\Filament\Student\Pages\Auth\StudentLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('student login page renders full screen with student branding', function () {
    $this->seed();

    $response = $this->get('/student/login');

    $response->assertOk()
        ->assertSee('slogin-shell', false)
        ->assertSee('Student Portal')
        ->assertSee('Student Platform')
        ->assertDontSee('Laravel')
        ->assertDontSee('Teacher Portal');
});

test('student can sign in from the login page', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('student');

    Livewire::test(StudentLogin::class)
        ->fillForm([
            'email' => 'student1@example.com',
            'password' => '12345678',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->check())->toBeTrue();
});

test('student cannot sign in with wrong password', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('student');

    Livewire::test(StudentLogin::class)
        ->fillForm([
            'email' => 'student1@example.com',
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    expect(auth()->check())->toBeFalse();
});
