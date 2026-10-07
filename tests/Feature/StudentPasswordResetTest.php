<?php

use App\Filament\Student\Pages\Auth\RequestPasswordReset;
use App\Filament\Student\Pages\Auth\ResetPassword;
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
    Filament::setCurrentPanel(Filament::getPanel('student'));
});

test('student forgot password page renders with student branding', function () {
    $response = $this->get('/student/password-reset/request');

    $response->assertOk()
        ->assertSee('stureset-shell', false)
        ->assertSee('Reset your password')
        ->assertSee('Student Portal')
        ->assertSee('Back to Home');
});

test('student login page links to the password reset request page', function () {
    $this->get('/student/login')
        ->assertOk()
        ->assertSee('/student/password-reset/request', false);
});

test('student can request a password reset link', function () {
    Notification::fake();

    $user = User::where('email', 'student1@example.com')->firstOrFail();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($user, FilamentResetPasswordNotification::class, function ($notification) {
        return str_contains($notification->url, '/student/password-reset/reset');
    });
});

test('super admin accounts do not receive a reset link from the student panel', function () {
    Notification::fake();

    $superAdmin = User::where('email', 'admin1@example.com')->firstOrFail();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $superAdmin->email])
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

test('student can reset the password with a valid token', function () {
    $user = User::where('email', 'student1@example.com')->firstOrFail();
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

test('student reset form validates the strong password rule', function () {
    $user = User::where('email', 'student1@example.com')->firstOrFail();
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
    $user = User::where('email', 'student1@example.com')->firstOrFail();
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

    $user = User::where('email', 'student1@example.com')->firstOrFail();

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
