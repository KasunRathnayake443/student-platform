<?php

use App\Filament\Pages\PlatformSettings;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\PlatformSettings as PlatformSettingsService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function psSuperAdmin(): User
{
    return User::where('email', 'admin1@example.com')->firstOrFail();
}

function psSchoolAdmin(): User
{
    return User::where('email', 'schooladmin1@example.com')->firstOrFail();
}

test('guests are redirected from the platform settings page', function () {
    $this->get('/admin/platform-settings')->assertRedirect('/admin/login');
});

test('super admins can open the platform settings page', function () {
    $this->actingAs(psSuperAdmin())
        ->get('/admin/platform-settings')
        ->assertOk()
        ->assertSee('Platform Settings');
});

test('non-super admins cannot access platform settings', function () {
    $this->actingAs(psSchoolAdmin())
        ->get('/admin/platform-settings')
        ->assertForbidden();
});

test('super admin can save platform branding settings', function () {
    Livewire::actingAs(psSuperAdmin())
        ->test(PlatformSettings::class)
        ->fillForm([
            'platform_name' => 'EduCloud',
            'platform_tagline' => 'Learn anywhere',
        ])
        ->call('save');

    $settings = PlatformSetting::settings();

    expect($settings->platform_name)->toBe('EduCloud')
        ->and($settings->platform_tagline)->toBe('Learn anywhere')
        ->and(config('app.name'))->toBe('EduCloud');
});

test('super admin can save password reset email settings', function () {
    Livewire::actingAs(psSuperAdmin())
        ->test(PlatformSettings::class)
        ->fillForm([
            'platform_name' => 'EduCloud',
            'mail_from_address' => 'no-reply@educloud.test',
            'mail_from_name' => 'EduCloud',
            'mail_host' => 'smtp.mail.test',
            'mail_port' => 587,
            'mail_username' => 'smtp@educloud.test',
            'mail_password' => 'secret-app-password',
            'mail_encryption' => 'tls',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = PlatformSetting::settings();

    expect($settings->mail_host)->toBe('smtp.mail.test')
        ->and($settings->mail_password)->toBe('secret-app-password')
        ->and((string) config('mail.mailers.smtp.host'))->toBe('smtp.mail.test')
        ->and((string) config('mail.mailers.smtp.port'))->toBe('587')
        ->and((string) config('mail.mailers.smtp.username'))->toBe('smtp@educloud.test')
        ->and(config('mail.from.address'))->toBe('no-reply@educloud.test')
        ->and(config('mail.from.name'))->toBe('EduCloud');
});

test('blank smtp password keeps the stored password', function () {
    PlatformSetting::settings()->update(['mail_password' => 'keep-me']);
    PlatformSettingsService::flushCache();

    Livewire::actingAs(psSuperAdmin())
        ->test(PlatformSettings::class)
        ->fillForm([
            'mail_host' => 'smtp.example.com',
        ])
        ->call('save');

    expect(PlatformSetting::settings()->mail_password)->toBe('keep-me');
});

test('empty email settings do not override the default mail configuration', function () {
    $originalHost = config('mail.mailers.smtp.host');

    Livewire::actingAs(psSuperAdmin())
        ->test(PlatformSettings::class)
        ->fillForm([
            'platform_name' => 'Name Only',
        ])
        ->call('save');

    expect(config('mail.mailers.smtp.host'))->toBe($originalHost)
        ->and(config('app.name'))->toBe('Name Only');
});

test('test email is sent from the saved platform email address', function () {
    app()->forgetInstance('mail.manager');
    app()->forgetInstance('mailer');

    PlatformSetting::settings()->update([
        'mail_from_address' => 'no-reply@educloud.test',
        'mail_from_name' => 'EduCloud',
    ]);
    PlatformSettingsService::flushCache();
    PlatformSettingsService::apply();

    Livewire::actingAs(psSuperAdmin())
        ->test(PlatformSettings::class)
        ->callAction('sendTestEmail', [
            'recipient' => 'someone@example.test',
        ]);

    $messages = app('mailer')->getSymfonyTransport()->messages();

    expect($messages)->toHaveCount(1);

    $from = $messages[0]->getOriginalMessage()->getFrom();

    expect($from[0]->getAddress())->toBe('no-reply@educloud.test');
});

test('saved platform logo is served by the platform logo route', function () {
    $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));
    $disk->put('platform/logo/brand.png', 'fake-png-bytes');

    PlatformSetting::settings()->update(['platform_logo' => 'platform/logo/brand.png']);
    PlatformSettingsService::flushCache();

    $this->get('/platform/logo')
        ->assertOk();
});

test('the platform logo route is publicly accessible to guests', function () {
    $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));
    $disk->put('platform/logo/brand.png', 'fake-png-bytes');

    PlatformSetting::settings()->update(['platform_logo' => 'platform/logo/brand.png']);
    PlatformSettingsService::flushCache();

    $this->get('/platform/logo')
        ->assertOk()
        ->assertHeader('content-type', 'image/png');
});

test('the platform logo route responds 404 when no logo is saved', function () {
    PlatformSetting::settings()->update(['platform_logo' => null]);
    PlatformSettingsService::flushCache();

    $this->get('/platform/logo')
        ->assertNotFound();
});

test('logoUrl falls back to the default asset when no logo is configured', function () {
    PlatformSetting::settings()->update(['platform_logo' => null]);
    PlatformSettingsService::flushCache();

    expect(PlatformSettingsService::logoUrl())->toContain('images/platform-logo.svg');
});

test('student dashboard renders the configured platform logo', function () {
    $disk = Storage::disk((string) config('filament.default_filesystem_disk', 'local'));
    $disk->put('platform/logo/brand.png', 'fake-png-bytes');

    PlatformSetting::settings()->update(['platform_logo' => 'platform/logo/brand.png']);
    PlatformSettingsService::flushCache();

    $this->actingAs(User::where('email', 'student1@example.com')->firstOrFail())
        ->get('/student')
        ->assertOk()
        ->assertSee('/platform/logo', false);
});
