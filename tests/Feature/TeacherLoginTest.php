<?php

use App\Filament\Teacher\Pages\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('teacher login page renders full screen with teacher branding', function () {
    $this->seed();

    $response = $this->get('/teacher/login');

    $response->assertOk()
        ->assertSee('tlogin-shell', false)
        ->assertSee('Teacher Portal')
        ->assertSee('Student Platform')
        ->assertDontSee('Laravel');
});

test('teacher can sign in from the login page', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('teacher');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'teacher1@example.com',
            'password' => '12345678',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->check())->toBeTrue();
});

test('teacher cannot sign in with wrong password', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('teacher');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'teacher1@example.com',
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    expect(auth()->check())->toBeFalse();
});
