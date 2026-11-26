<?php

use App\Filament\Resources\ContactSubmissions\Pages\ListContactSubmissions;
use App\Models\ContactSubmission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function csSuperAdmin(): User
{
    return User::where('email', 'admin1@example.com')->firstOrFail();
}

function csSchoolAdmin(): User
{
    return User::where('email', 'schooladmin1@example.com')->firstOrFail();
}

test('contact submissions appear in the super admin navigation', function () {
    $this->actingAs(csSuperAdmin())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Contact Submissions')
        ->assertSee('/admin/contact-submissions', false);
});

test('guests are redirected from contact submissions', function () {
    $this->get('/admin/contact-submissions')->assertRedirect('/admin/login');
});

test('non-super admins cannot access contact submissions', function () {
    $this->actingAs(csSchoolAdmin())
        ->get('/admin/contact-submissions')
        ->assertForbidden();
});

test('super admin can view and delete contact submissions', function () {
    $submission = ContactSubmission::query()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'phone' => '+94 77 111 2222',
        'message' => 'Please send me a demo.',
    ]);

    Livewire::actingAs(csSuperAdmin())
        ->test(ListContactSubmissions::class)
        ->assertSee('Ada Lovelace')
        ->assertSee('ada@example.test')
        ->assertSee('Please send me a demo.')
        ->callTableAction('delete', $submission->getKey());

    expect(ContactSubmission::query()->count())->toBe(0);
});
