<?php

use App\Models\ContactSubmission;
use App\Models\PlatformSetting;
use App\Services\PlatformSettings as PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function configureLandingContact(): void
{
    PlatformSetting::settings()->update([
        'contact_address' => '12 Main Street, Colombo 02',
        'contact_email' => 'hello@school.test',
        'contact_phone' => '+94 77 123 4567',
        'facebook_url' => 'https://facebook.com/beesmart',
        'youtube_url' => 'https://youtube.com/@beesmart',
        'tiktok_url' => 'https://tiktok.com/@beesmart',
        'instagram_url' => 'https://instagram.com/beesmart',
    ]);

    PlatformSettingsService::flushCache();
}

test('the landing page shows the configured contact details', function () {
    configureLandingContact();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('12 Main Street, Colombo 02')
        ->assertSee('mailto:hello@school.test', false)
        ->assertSee('tel:+94771234567', false)
        ->assertSee('+94 77 123 4567')
        ->assertSee('https://facebook.com/beesmart', false)
        ->assertSee('https://youtube.com/@beesmart', false)
        ->assertSee('https://tiktok.com/@beesmart', false)
        ->assertSee('https://instagram.com/beesmart', false)
        ->assertSee('aria-label="Facebook"', false)
        ->assertSee('aria-label="YouTube"', false)
        ->assertSee('aria-label="TikTok"', false)
        ->assertSee('aria-label="Instagram"', false)
        ->assertSee('id="contact-form"', false)
        ->assertSee('id="contact-form-feedback"', false)
        ->assertSee('Send a message');

    $html = $response->getContent();

    expect(strpos($html, 'id="contact-name"'))
        ->toBeLessThan(strpos($html, 'Contact details'));
});

test('the contact form stores a submission and returns to the contact section', function () {
    $response = $this->post(route('contact.store'), [
        'name' => 'Kasun Perera',
        'email' => 'kasun@example.test',
        'phone' => '+94 77 999 8888',
        'message' => 'Please contact me about a demo.',
    ]);

    $response->assertRedirect(route('home').'#contact')
        ->assertSessionHas('contact_submitted', true);

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'Kasun Perera',
        'email' => 'kasun@example.test',
        'phone' => '+94 77 999 8888',
        'message' => 'Please contact me about a demo.',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Thanks! Your message has been sent.');
});

test('the contact form accepts ajax submissions without a redirect', function () {
    $response = $this->postJson(route('contact.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'phone' => '+94 77 555 4444',
        'message' => 'Sending this without leaving the page.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Thanks! Your message has been sent.');

    $this->assertDatabaseHas('contact_submissions', [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'message' => 'Sending this without leaving the page.',
    ]);
});

test('the contact form validates required fields', function () {
    $this->post(route('contact.store'), [
        'name' => '',
        'email' => 'not-an-email',
        'message' => '',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(ContactSubmission::query()->count())->toBe(0);
});

test('the contact form is rate limited', function () {
    $payload = [
        'name' => 'Spam Sender',
        'email' => 'spam@example.test',
        'message' => 'Repeated message.',
    ];

    for ($i = 0; $i < 6; $i++) {
        $this->post(route('contact.store'), $payload);
    }

    expect(ContactSubmission::query()->count())->toBe(5);

    $this->post(route('contact.store'), $payload)
        ->assertSessionHasErrors(['contact']);
});
