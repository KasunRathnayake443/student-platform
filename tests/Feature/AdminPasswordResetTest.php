<?php

use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Pages\Auth\ResetPassword;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as FilamentResetPasswordNotification;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('super admin forgot password page renders with admin branding', function () {
    $response = $this->get('/admin/password-reset/request');

    $response->assertOk()
        ->assertSee('areset-shell', false)
        ->assertSee('Reset your password')
        ->assertSee('Super Admin Portal')
        ->assertSee('Back to Home');
});

test('super admin login page links to the password reset request page', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('/admin/password-reset/request', false);
});

test('super admin can request a password reset link', function () {
    Notification::fake();

    $user = User::where('email', 'admin1@example.com')->firstOrFail();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($user, FilamentResetPasswordNotification::class, function ($notification) {
        return str_contains($notification->url, '/admin/password-reset/reset');
    });
});

test('non-admin accounts do not receive a reset link from the admin panel', function () {
    Notification::fake();

    $teacher = User::where('email', 'teacher1@example.com')->firstOrFail();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $teacher->email])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertNothingSent();
});

test('unknown emails do not receive a reset link and produce no form errors', function () {
    Notification::fake();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => 'nobody@example.com'])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertNothingSent();
});

test('super admin can reset the password with a valid token', function () {
    $user = User::where('email', 'admin1@example.com')->firstOrFail();
    $token = Password::broker()->createToken($user);

    Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
        ->fillForm([
            'password' => 'newsecret42',
            'passwordConfirmation' => 'newsecret42',
        ])
        ->call('resetPassword')
        ->assertHasNoFormErrors();

    expect(Hash::check('newsecret42', $user->fresh()->password))->toBeTrue();
});

test('super admin reset form validates the strong password rule', function () {
    $user = User::where('email', 'admin1@example.com')->firstOrFail();
    $token = Password::broker()->createToken($user);

    Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
        ->fillForm([
            'password' => 'short',
            'passwordConfirmation' => 'short',
        ])
        ->call('resetPassword')
        ->assertHasFormErrors(['password']);

    expect(Hash::check('short', $user->fresh()->password))->toBeFalse();
});

test('reset notifications are dispatched on the sync connection', function () {
    $user = User::where('email', 'admin1@example.com')->firstOrFail();
    $token = Password::broker()->createToken($user);

    $notification = app(FilamentResetPasswordNotification::class, ['token' => $token]);

    expect($notification)->toBeInstanceOf(FilamentResetPasswordNotification::class)
        ->and($notification->connection)->toBe('sync');
});

test('reset emails are sent from the single configured password reset address', function () {
    config(['mail.from.address' => 'no-reply@student-platform.test']);
    config(['mail.from.name' => 'Student Platform']);

    app()->forgetInstance('mail.manager');
    app()->forgetInstance('mailer');
    app()->forgetInstance('notifications');
    app()->forgetInstance(ChannelManager::class);

    $user = User::where('email', 'admin1@example.com')->firstOrFail();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors();

    $messages = app('mailer')->getSymfonyTransport()->messages();

    expect($messages)->toHaveCount(1);

    $from = $messages[0]->getOriginalMessage()->getFrom();

    expect($from)->not->toBeEmpty()
        ->and($from[0]->getAddress())->toBe('no-reply@student-platform.test');
});
