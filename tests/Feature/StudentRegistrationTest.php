<?php

use App\Filament\Pages\StudentRegistrations;
use App\Models\PlatformSetting;
use App\Models\StudentRegistration;
use App\Models\User;
use App\Services\PlatformSettings as PlatformSettingsService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function configureStudentRegistration(): void
{
    PlatformSetting::settings()->update([
        'registration_fee' => '2500.00',
        'bank_name' => 'Commercial Bank',
        'bank_account_name' => 'BeeSmart (Pvt) Ltd',
        'bank_account_number' => '1234567890',
        'bank_branch' => 'Colombo 02',
        'ezcash_number' => '077 123 4567',
        'ezcash_merchant_alias' => 'BeeSmart',
        'mcash_number' => '071 765 4321',
        'mcash_merchant_id' => 'MC-1234',
        'registration_languages' => ['English', 'සිංහල', 'தமிழ்'],
        'registration_grades' => ['Grade 6', 'Grade 7'],
        'contact_address' => '12 Main Street, Colombo 02',
        'contact_email' => 'hello@beesmart.test',
        'contact_phone' => '+94 77 123 4567',
    ]);

    PlatformSettingsService::flushCache();
}

test('the landing header links to the student registration page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Register now')
        ->assertSee(route('student.registration'), false);
});

test('the registration page uses the shared header with all three languages', function () {
    $this->get(route('student.registration'))
        ->assertOk()
        ->assertSee('id="landing-header"', false)
        ->assertSee('id="menu-btn"', false)
        ->assertSee('English')
        ->assertSee('සිංහල')
        ->assertSee('தமிழ்')
        ->assertSee(route('locale.switch', 'si'), false);
});

test('the registration page shows the configured details and choices', function () {
    configureStudentRegistration();

    $this->get(route('student.registration'))
        ->assertOk()
        ->assertSee('This registration page is for students only')
        ->assertSee(route('home').'#contact', false)
        ->assertSee('12 Main Street, Colombo 02')
        ->assertSee('hello@beesmart.test')
        ->assertSee('LKR 2,500.00')
        ->assertSee('Commercial Bank')
        ->assertSee('1234567890')
        ->assertSee('077 123 4567')
        ->assertSee('071 765 4321')
        ->assertSee('Grade 6')
        ->assertSee('Grade 7')
        ->assertSee('සිංහල')
        ->assertSee('id="receipt-preview"', false);
});

test('a student can submit the registration form with a bank receipt', function () {
    Storage::fake('public');

    $response = $this->post(route('student.registration.store'), [
        'full_name' => 'Ada Perera',
        'institution' => 'St. Bridgets College',
        'grade' => 'Grade 7',
        'language' => 'English',
        'email' => 'ada@example.test',
        'phone' => '0779998888',
        'referred_by' => 'A friend',
        'payment_method' => 'bank_transfer',
        'payment_date' => now()->toDateString(),
        'transaction_reference' => 'TRX-123456',
        'receipt' => UploadedFile::fake()->create('slip.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertRedirect(route('student.registration'))
        ->assertSessionHas('registration_submitted', true);

    $registration = StudentRegistration::query()->firstOrFail();

    expect($registration->full_name)->toBe('Ada Perera')
        ->and($registration->payment_method)->toBe('bank_transfer')
        ->and($registration->status)->toBe('pending')
        ->and($registration->receipt_path)->not->toBeNull();

    Storage::disk('public')->assertExists($registration->receipt_path);

    $this->assertDatabaseHas('student_registrations', [
        'email' => 'ada@example.test',
        'transaction_reference' => 'TRX-123456',
    ]);
});

test('the receipt is required for bank transfers but not for mobile payments', function () {
    $payload = [
        'full_name' => 'Kasun Silva',
        'institution' => 'Royal College',
        'grade' => 'Grade 6',
        'language' => 'English',
        'email' => 'kasun@example.test',
        'phone' => '0771112222',
        'payment_method' => 'ezcash',
        'payment_date' => now()->toDateString(),
        'transaction_reference' => 'SMS-998877',
    ];

    $this->post(route('student.registration.store'), array_merge($payload, ['payment_method' => 'bank_transfer']))
        ->assertSessionHasErrors(['receipt']);

    expect(StudentRegistration::query()->count())->toBe(0);

    $this->post(route('student.registration.store'), $payload)
        ->assertRedirect(route('student.registration'));

    expect(StudentRegistration::query()->count())->toBe(1);
});

test('super admins can save the registration settings', function () {
    Livewire::actingAs(User::where('email', 'admin1@example.com')->firstOrFail())
        ->test(StudentRegistrations::class)
        ->fillForm([
            'registration_fee' => '3500',
            'bank_name' => 'Bank of Ceylon',
            'bank_account_name' => 'BeeSmart (Pvt) Ltd',
            'bank_account_number' => '9876543210',
            'bank_branch' => 'Kandy',
            'ezcash_number' => '077 000 1111',
            'ezcash_merchant_alias' => 'BeeSmartPay',
            'mcash_number' => '071 000 2222',
            'mcash_merchant_id' => 'MC-9999',
            'registration_languages' => ['English', 'සිංහල'],
            'registration_grades' => ['Grade 1', 'Grade 2'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = PlatformSetting::settings();

    expect($settings->registration_fee)->toBe('3500.00')
        ->and($settings->bank_name)->toBe('Bank of Ceylon')
        ->and($settings->ezcash_number)->toBe('077 000 1111')
        ->and($settings->registration_languages)->toBe(['English', 'සිංහල'])
        ->and($settings->registration_grades)->toBe(['Grade 1', 'Grade 2']);

    $this->get(route('student.registration'))
        ->assertOk()
        ->assertSee('LKR 3,500.00')
        ->assertSee('Bank of Ceylon')
        ->assertSee('Grade 2');
});

test('super admins can see registration submissions from the same page', function () {
    StudentRegistration::query()->create([
        'full_name' => 'Nimal Fernando',
        'institution' => 'Trinity College',
        'grade' => 'Grade 6',
        'language' => 'English',
        'email' => 'nimal@example.test',
        'phone' => '0771231231',
        'payment_method' => 'mcash',
        'payment_date' => now()->toDateString(),
        'transaction_reference' => 'MC-REF-1',
    ]);

    $this->actingAs(User::where('email', 'admin1@example.com')->firstOrFail())
        ->get('/admin/student-registrations')
        ->assertOk()
        ->assertSee('Student Registrations');

    Livewire::actingAs(User::where('email', 'admin1@example.com')->firstOrFail())
        ->test(StudentRegistrations::class)
        ->assertSee('Nimal Fernando')
        ->assertSee('MC-REF-1')
        ->assertSee('Registration Settings');
});

test('the registration page is translated into Sinhala', function () {
    $this->get(route('locale.switch', 'si'));

    $this->get(route('student.registration'))
        ->assertOk()
        ->assertSee('ඔබේ ඉගෙනුම් ගමන')
        ->assertSee('මෙම ලියාපදිංචි පිටුව සිසුන් සඳහා පමණි')
        ->assertSee('සිසු ලියාපදිංචි පෝරමය')
        ->assertSee('ලියාපදිංචිය ඉදිරිපත් කරන්න');
});

test('the registration page is translated into Tamil', function () {
    $this->get(route('locale.switch', 'ta'));

    $this->get(route('student.registration'))
        ->assertOk()
        ->assertSee('உங்கள் கல்விப் பயணம்')
        ->assertSee('இந்தப் பதிவு பக்கம் மாணவர்களுக்காக மட்டுமே')
        ->assertSee('மாணவர் பதிவு படிவம்')
        ->assertSee('பதிவைச் சமர்ப்பிக்கவும்');
});
