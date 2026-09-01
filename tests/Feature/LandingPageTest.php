<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the landing page renders portal selection for student, teacher and school admin', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Choose your')
        ->assertSee('Student')
        ->assertSee('/student/login', false)
        ->assertSee('Teacher')
        ->assertSee('/teacher/login', false)
        ->assertSee('School Admin')
        ->assertSee('/school-admin/login', false);
});

test('the landing page does not expose the super admin login', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('/admin/login', false);
});

test('the school admin login URL is reserved and reachable', function () {
    $this->get(route('school-admin.login'))
        ->assertOk()
        ->assertSee('School Admin portal')
        ->assertSee('coming soon');
});
