<?php

use App\Filament\Resources\Schools\Pages\EditSchool as AdminEditSchool;
use App\Filament\SchoolAdmin\Resources\Schools\Pages\EditSchool as SchoolAdminEditSchool;
use App\Models\School;
use App\Models\User;
use App\Notifications\SchoolTestEmailNotification;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function testEmailSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function testEmailUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function testEmailConfigure(School $school): School
{
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

test('both school edit screens expose the test email action', function () {
    $this->seed();
    $school = testEmailConfigure(testEmailSchool());
    $admin = testEmailUser('admin1@example.com');
    $schoolAdmin = testEmailUser('schooladmin1@example.com');

    Livewire::actingAs($admin)
        ->test(AdminEditSchool::class, ['record' => $school->getKey()])
        ->assertActionExists('sendTestEmail')
        ->assertSee('Send Test Email');

    Filament::setCurrentPanel('school-admin');

    Livewire::actingAs($schoolAdmin)
        ->test(SchoolAdminEditSchool::class, ['record' => $school->getKey()])
        ->assertActionExists('sendTestEmail')
        ->assertSee('Send Test Email');
});

test('school edit action sends a test notification using the saved mailer', function () {
    $this->seed();
    $school = testEmailConfigure(testEmailSchool());
    $admin = testEmailUser('admin1@example.com');
    Notification::fake();

    Livewire::actingAs($admin)
        ->test(AdminEditSchool::class, ['record' => $school->getKey()])
        ->callAction('sendTestEmail', [
            'recipient' => 'qa@example.com',
        ])
        ->assertHasNoActionErrors();

    Notification::assertSentOnDemand(
        SchoolTestEmailNotification::class,
        fn (SchoolTestEmailNotification $notification, array $channels): bool => $notification->schoolId === $school->getKey()
            && $channels === ['mail'],
    );
});

test('school edit action rejects an invalid recipient and blocks unconfigured delivery', function () {
    $this->seed();
    $school = testEmailSchool();
    $admin = testEmailUser('admin1@example.com');
    Notification::fake();

    Livewire::actingAs($admin)
        ->test(AdminEditSchool::class, ['record' => $school->getKey()])
        ->callAction('sendTestEmail', [
            'recipient' => 'not-an-email',
        ])
        ->assertHasActionErrors(['recipient']);

    Livewire::actingAs($admin)
        ->test(AdminEditSchool::class, ['record' => $school->getKey()])
        ->callAction('sendTestEmail', [
            'recipient' => 'qa@example.com',
        ])
        ->assertHasNoActionErrors();

    Notification::assertNothingSent();
});

test('test notification uses the school sender and dynamic mailer', function () {
    $this->seed();
    $school = testEmailConfigure(testEmailSchool());
    $notification = new SchoolTestEmailNotification($school->getKey());

    $message = $notification->toMail(new AnonymousNotifiable);
    $configuration = config('mail.mailers.'.$message->mailer);

    expect($message->from)->toBe([$school->email, $school->name])
        ->and($message->subject)->toBe('SMTP test - '.$school->name)
        ->and($configuration['host'])->toBe('smtp.horizon.example')
        ->and($configuration['require_tls'])->toBeTrue();
});
