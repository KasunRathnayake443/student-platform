<?php

use App\Filament\Pages\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin login page renders full screen with super admin branding', function () {
    $this->seed();

    $response = $this->get('/admin/login');

    $response->assertOk()
        ->assertSee('alogin-shell', false)
        ->assertSee('Super Admin Portal')
        ->assertSee('Student Platform')
        ->assertDontSee('Laravel')
        ->assertDontSee('Teacher Portal')
        ->assertDontSee('Student Portal');
});

test('admin can sign in from the login page', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('admin');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'admin1@example.com',
            'password' => '12345678',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(auth()->check())->toBeTrue();
});

test('admin cannot sign in with wrong password', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('admin');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'admin1@example.com',
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    expect(auth()->check())->toBeFalse();
});
