<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the landing page renders portal selection for student, teacher, school admin and parent', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Choose your')
        ->assertSee('Student')
        ->assertSee('/student/login', false)
        ->assertSee('Teacher')
        ->assertSee('/teacher/login', false)
        ->assertSee('School Admin')
        ->assertSee('/school-admin/login', false)
        ->assertSee('Parent')
        ->assertSee('/parent/login', false);
});

test('the landing page does not expose the super admin login', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('/admin/login', false);
});

test('the school admin login URL is reserved and reachable', function () {
    $this->get('/school-admin/login')
        ->assertOk()
        ->assertSee('School Admin Portal');
});

test('the language switcher stores the locale and translates the landing page', function () {
    $this->from(route('home'))
        ->get(route('locale.switch', 'si'))
        ->assertRedirect(route('home'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('සම්පූර්ණ ඉගෙනුම් කළමනාකරණ පද්ධතිය')
        ->assertSee('සිංහල');
});

test('tamil can be selected as the landing page locale', function () {
    $this->from(route('home'))
        ->get(route('locale.switch', 'ta'))
        ->assertRedirect(route('home'));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('முழுமையான கற்றல் மேலாண்மை அமைப்பு')
        ->assertSee('தமிழ்');
});

test('an unsupported locale is rejected', function () {
    $this->get(route('locale.switch', 'fr'))->assertNotFound();
});
