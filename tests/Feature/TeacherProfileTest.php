<?php

use App\Filament\Teacher\Pages\MyProfile;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function prfUser(string $email): User
{
    return User::where('email', $email)->firstOrFail();
}

function prfTeacherByNo(string $employeeNo): Teacher
{
    return Teacher::where('employee_no', $employeeNo)->firstOrFail();
}

test('teacher dashboard shows my profile link and letter avatar fallback', function () {
    $this->seed();
    $this->actingAs(prfUser('teacher1@example.com'));

    expect(prfTeacherByNo('EMP-T-1001')->profile_photo_url)->toBeNull();

    $this->get('/teacher')
        ->assertOk()
        ->assertSee('My Profile')
        ->assertSee('t-avatar', false)
        ->assertSee('D', false);
});

test('topbar avatar shows the profile picture when the teacher has one', function () {
    $this->seed();
    Storage::disk((string) config('filament.default_filesystem_disk', 'local'))
        ->put('teachers/profile/test-photo.jpg', 'fake-image-contents');

    $teacher = prfTeacherByNo('EMP-T-1001');
    $teacher->update(['profile_photo' => 'teachers/profile/test-photo.jpg']);
    $this->actingAs($teacher->user);

    $url = $teacher->fresh()->profile_photo_url;

    expect($url)->not->toBeNull()
        ->and($url)->toContain('/teachers/'.$teacher->getKey().'/profile-photo');

    $this->get('/teacher')
        ->assertOk()
        ->assertSee('<img src="'.e($url).'"', false);

    $this->get($url)
        ->assertOk();
});

test('profile photo endpoint is protected and returns 404 when missing', function () {
    $this->seed();
    $teacher = prfTeacherByNo('EMP-T-1001');

    $this->get('/teachers/'.$teacher->getKey().'/profile-photo')
        ->assertRedirect(route('login'));

    $this->actingAs(prfUser('student1@example.com'));

    $this->get('/teachers/'.Student::firstOrFail()->getKey().'/profile-photo')
        ->assertNotFound();

    $this->actingAs(prfUser('teacher2@example.com'));

    $this->get('/teachers/'.$teacher->getKey().'/profile-photo')
        ->assertNotFound();
});

test('my profile page renders with current teacher data', function () {
    $this->seed();
    $this->actingAs(prfUser('teacher1@example.com'));

    $response = $this->get(MyProfile::getUrl(panel: 'teacher'));

    $response->assertOk()
        ->assertSee('Manage Profile')
        ->assertSee('Dr. Robert Langdon')
        ->assertSee('teacher1@example.com');
});

test('teacher can update their profile details', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('teacher');

    $teacher = prfTeacherByNo('EMP-T-1002');
    $this->actingAs($teacher->user);

    Livewire::test(MyProfile::class)
        ->fillForm([
            'name' => 'Sarah J. Connor',
            'phone' => '+94 71 999 8877',
            'address' => '1 Resistance Avenue, Colombo',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($teacher->user->fresh()->name)->toBe('Sarah J. Connor')
        ->and($teacher->user->fresh()->email)->toBe('teacher2@example.com')
        ->and($teacher->fresh()->phone)->toBe('+94 71 999 8877')
        ->and($teacher->fresh()->address)->toBe('1 Resistance Avenue, Colombo');
});

test('teacher can change their password from the profile page', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('teacher');

    $teacher = prfTeacherByNo('EMP-T-1002');
    $oldHash = $teacher->user->password;
    $this->actingAs($teacher->user);

    Livewire::test(MyProfile::class)
        ->fillForm([
            'new_password' => 'new-secret-123',
            'new_password_confirmation' => 'new-secret-123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $teacher->user->fresh();

    expect(Hash::check('new-secret-123', $fresh->password))->toBeTrue()
        ->and($fresh->password)->not->toBe($oldHash)
        ->and((bool) $fresh->must_change_password)->toBeFalse();
});

test('mismatched password confirmation is rejected', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('teacher');

    $teacher = prfTeacherByNo('EMP-T-1002');
    $oldHash = $teacher->user->password;
    $this->actingAs($teacher->user);

    Livewire::test(MyProfile::class)
        ->fillForm([
            'new_password' => 'new-secret-123',
            'new_password_confirmation' => 'different',
        ])
        ->call('save');

    expect($teacher->user->fresh()->password)->toBe($oldHash);
});

test('changing email resets verification', function () {
    $this->seed();
    Filament\Facades\Filament::setCurrentPanel('teacher');

    $teacher = prfTeacherByNo('EMP-T-1002');
    expect($teacher->user->email_verified_at)->not->toBeNull();

    $this->actingAs($teacher->user);

    Livewire::test(MyProfile::class)
        ->fillForm(['email' => 'sarah.connor@example.com'])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $teacher->user->fresh();

    expect($fresh->email)->toBe('sarah.connor@example.com')
        ->and($fresh->email_verified_at)->toBeNull();
});
