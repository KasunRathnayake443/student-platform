<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the teacher sidebar links to the email composer', function () {
    $this->seed();
    $teacher = User::where('email', 'teacher1@example.com')->firstOrFail();

    $this->actingAs($teacher);

    $response = $this->get('/teacher');

    $response->assertOk();
    $response->assertSee('Email Students');
    $response->assertSee(route('filament.teacher.pages.compose-student-email'), false);
});

test('the composer page renders and its own sidebar link is active', function () {
    $this->seed();
    $teacher = User::where('email', 'teacher1@example.com')->firstOrFail();

    $this->actingAs($teacher);

    $response = $this->get(route('filament.teacher.pages.compose-student-email'));

    $response->assertOk();
    $response->assertSee('Recipients');
    $response->assertSee('Send Email');
});
