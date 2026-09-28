<?php

use App\Filament\SchoolAdmin\Resources\Schools\Pages\EditSchool;
use App\Models\School;
use App\Models\User;
use App\Services\SchoolMailTransport;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function smtpFeatureSchool(): School
{
    return School::where('code', 'HIA-2026')->firstOrFail();
}

function smtpFeatureUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function smtpFeatureConfigure(School $school, string $host = 'smtp.horizon.example'): School
{
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => $host,
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    return $school->fresh();
}

test('school smtp passwords are encrypted at rest', function () {
    $this->seed();
    $school = smtpFeatureConfigure(smtpFeatureSchool());

    $rawPassword = DB::table('schools')
        ->where('id', $school->getKey())
        ->value('smtp_password');

    expect($rawPassword)->toBeString()
        ->and($rawPassword)->not->toBe('smtp-secret')
        ->and($school->fresh()->smtp_password)->toBe('smtp-secret')
        ->and($school->fresh()->toArray())->not->toHaveKey('smtp_password');
});

test('school mail transport uses isolated smtp configuration', function () {
    $this->seed();
    $school = smtpFeatureConfigure(smtpFeatureSchool());

    $mailer = app(SchoolMailTransport::class)->configure($school);
    $configuration = config("mail.mailers.{$mailer}");

    expect($mailer)->toStartWith("school-{$school->getKey()}-")
        ->and($configuration['host'])->toBe('smtp.horizon.example')
        ->and($configuration['port'])->toBe(587)
        ->and($configuration['username'])->toBe('notifications@horizon.example')
        ->and($configuration['password'])->toBe('smtp-secret')
        ->and($configuration['auto_tls'])->toBeTrue()
        ->and($configuration['require_tls'])->toBeTrue();
});

test('school mail transport maps ssl and plaintext modes', function () {
    $this->seed();
    $school = smtpFeatureSchool();
    $transport = app(SchoolMailTransport::class);

    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => 'smtp-secret',
        'smtp_port' => 465,
        'smtp_encryption' => 'ssl',
    ]);
    $sslMailer = $transport->configure($school->fresh());
    $sslConfiguration = config("mail.mailers.{$sslMailer}");

    expect($sslConfiguration['scheme'])->toBe('smtps')
        ->and($sslConfiguration['auto_tls'])->toBeFalse()
        ->and($sslConfiguration['require_tls'])->toBeFalse();

    $school->update([
        'smtp_port' => 25,
        'smtp_encryption' => 'none',
    ]);
    $plainMailer = $transport->configure($school->fresh());
    $plainConfiguration = config("mail.mailers.{$plainMailer}");

    expect($plainConfiguration['scheme'])->toBe('smtp')
        ->and($plainConfiguration['auto_tls'])->toBeFalse()
        ->and($plainConfiguration['require_tls'])->toBeFalse();
});

test('school mail transport rejects incomplete settings', function () {
    $this->seed();
    $school = smtpFeatureSchool();
    $school->update([
        'email' => 'notifications@horizon.example',
        'smtp_host' => 'smtp.horizon.example',
        'smtp_password' => null,
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
    ]);

    expect(fn () => app(SchoolMailTransport::class)->configure($school->fresh()))
        ->toThrow(InvalidArgumentException::class, 'The school SMTP configuration is incomplete.');
});

test('school admin can rotate smtp settings without exposing or overwriting a blank password', function () {
    $this->seed();
    $school = smtpFeatureConfigure(smtpFeatureSchool());
    $rawBefore = DB::table('schools')
        ->where('id', $school->getKey())
        ->value('smtp_password');

    $this->actingAs(smtpFeatureUser('schooladmin1@example.com'));
    Filament::setCurrentPanel('school-admin');

    Livewire::test(EditSchool::class, ['record' => $school->getKey()])
        ->assertSuccessful()
        ->assertFormSet(['smtp_password' => null])
        ->fillForm(['name' => 'Horizon International Academy Updated'])
        ->call('save')
        ->assertHasNoFormErrors();

    $rawAfterBlankSave = DB::table('schools')
        ->where('id', $school->getKey())
        ->value('smtp_password');

    expect($rawAfterBlankSave)->toBe($rawBefore)
        ->and($school->fresh()->smtp_password)->toBe('smtp-secret');

    Livewire::test(EditSchool::class, ['record' => $school->getKey()])
        ->fillForm(['smtp_host' => 'smtp.rotated.example'])
        ->call('save')
        ->assertHasFormErrors(['smtp_password']);

    Livewire::test(EditSchool::class, ['record' => $school->getKey()])
        ->fillForm([
            'smtp_host' => 'smtp.rotated.example',
            'smtp_password' => 'rotated-secret',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($school->fresh()->smtp_host)->toBe('smtp.rotated.example')
        ->and($school->fresh()->smtp_password)->toBe('rotated-secret');
});
